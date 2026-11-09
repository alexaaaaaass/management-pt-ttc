<?php

namespace App\Services\Finance;

use App\Models\CoaClass;
use App\Models\CoaMovement;
use App\Models\Gaji;
use App\Models\Invoice;
use App\Models\InvPayment;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Karyawan;
use App\Models\MasterCOA;
use App\Models\OperasionalPay;
use App\Models\PoBilling;
use App\Models\PoBillPay;
use App\Models\PoBillPayItem;
use App\Models\TransKas;
use App\Models\TransKasBank;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TreasuryService
{
    /*
    |--------------------------------------------------------------------------
    | DEFAULT COA RESOLVER (INTEGRASI AKUNTANSI DOUBLE-ENTRY TTC & INDIGAMA)
    |--------------------------------------------------------------------------
    */

    /**
     * Cari akun MasterCOA berdasarkan prioritas kode_akun atau nama_akun,
     * dengan fallback pembuatan akun otomatis bila database masih kosong.
     */
    public function resolveCoa(array $codes, array $nameKeywords = [], array $fallbackAttributes = []): ?MasterCOA
    {
        foreach ($codes as $code) {
            $found = MasterCOA::where('kode_akun', $code)
                ->orWhere('kode_akun', 'like', $code . '%')
                ->orderByRaw('CASE WHEN kode_akun = ? THEN 0 ELSE 1 END', [$code])
                ->first();
            if ($found) {
                return $found;
            }
        }

        foreach ($nameKeywords as $keyword) {
            $found = MasterCOA::where('nama_akun', 'like', '%' . $keyword . '%')->first();
            if ($found) {
                return $found;
            }
        }

        if (! empty($fallbackAttributes)) {
            $karyawanId = Karyawan::value('id');
            $coaClassId = CoaClass::value('id');
            if ($karyawanId && $coaClassId) {
                return MasterCOA::firstOrCreate(
                    ['kode_akun' => $fallbackAttributes['kode_akun']],
                    array_merge([
                        'karyawan_id'         => $karyawanId,
                        'coa_class_id'        => $coaClassId,
                        'periode'             => now()->format('Y-m'),
                        'gudang'              => 'TTC',
                        'saldo_debit'         => 0,
                        'saldo_kredit'        => 0,
                        'nominal_default'     => 0,
                        'status'              => 'aktif',
                        'level'               => 3,
                        'posting'             => 'Yes',
                    ], $fallbackAttributes)
                );
            }
        }

        return null;
    }

    public function resolvePiutangCoa(?int $excludeCoaId = null): ?MasterCOA
    {
        $coa = $this->resolveCoa(
            ['1121', '112.001', '1120'],
            ['Piutang - Barang Jadi IDR', 'Piutang Usaha'],
            [
                'kode_akun'           => '1121',
                'nama_akun'           => 'Piutang - Barang Jadi IDR',
                'account_type'        => 'Asset',
                'account_class'       => 'Current - AR',
                'financial_statement' => 'Balance Sheet',
                'normal_balance'      => 'Debit',
            ]
        );

        if ($coa && $excludeCoaId && (int) $coa->id === (int) $excludeCoaId) {
            return MasterCOA::where('id', '!=', $excludeCoaId)
                ->where(function ($q) {
                    $q->where('kode_akun', 'like', '112%')
                        ->orWhere('nama_akun', 'like', '%Piutang%');
                })
                ->first();
        }

        return $coa;
    }

    public function resolvePendapatanPenjualanCoa(): ?MasterCOA
    {
        return $this->resolveCoa(
            ['4101', '401.001', '4100', '4000'],
            ['Penjualan Barang Jadi - IDR', 'Penjualan Barang Jadi', 'Pendapatan Usaha'],
            [
                'kode_akun'           => '4101',
                'nama_akun'           => 'Penjualan Barang Jadi - IDR',
                'account_type'        => 'Revenue',
                'account_class'       => 'Revenue - Sales',
                'financial_statement' => 'Income Statement',
                'normal_balance'      => 'Credit',
            ]
        );
    }

    public function resolvePpnKeluaranCoa(): ?MasterCOA
    {
        return $this->resolveCoa(
            ['2121', '214.001', '2120'],
            ['PPN Terutang', 'PPN Keluaran', 'Hutang Pajak'],
            [
                'kode_akun'           => '2121',
                'nama_akun'           => 'PPN Terutang',
                'account_type'        => 'Liability',
                'account_class'       => 'Current - Tax',
                'financial_statement' => 'Balance Sheet',
                'normal_balance'      => 'Credit',
            ]
        );
    }

    public function resolveUangMukaPenjualanCoa(): ?MasterCOA
    {
        return $this->resolveCoa(
            ['2151', '2150'],
            ['Uang Muka Penjualan - IDR', 'Uang Muka Penjualan'],
            [
                'kode_akun'           => '2151',
                'nama_akun'           => 'Uang Muka Penjualan - IDR',
                'account_type'        => 'Liability',
                'account_class'       => 'Customer Advance',
                'financial_statement' => 'Balance Sheet',
                'normal_balance'      => 'Credit',
            ]
        );
    }

    public function resolvePersediaanBahanCoa(): ?MasterCOA
    {
        return $this->resolveCoa(
            ['1141', '5101', '1140'],
            ['Persediaan Bahan Baku', 'Pemakaian Bahan Baku - Produksi'],
            [
                'kode_akun'           => '1141',
                'nama_akun'           => 'Persediaan Bahan Baku',
                'account_type'        => 'Asset',
                'account_class'       => 'Current - Inventory',
                'financial_statement' => 'Balance Sheet',
                'normal_balance'      => 'Debit',
            ]
        );
    }

    public function resolvePpnMasukanCoa(): ?MasterCOA
    {
        return $this->resolveCoa(
            ['1171', '1170'],
            ['PPN Masukan', 'Pajak Dibayar di Muka'],
            [
                'kode_akun'           => '1171',
                'nama_akun'           => 'PPN Masukan',
                'account_type'        => 'Asset',
                'account_class'       => 'Tax Receivable',
                'financial_statement' => 'Balance Sheet',
                'normal_balance'      => 'Debit',
            ]
        );
    }

    public function resolveHutangUsahaCoa(?int $excludeCoaId = null): ?MasterCOA
    {
        $coa = $this->resolveCoa(
            ['2111', '211.001', '2110'],
            ['Utang Usaha - Bahan Baku IDR', 'Utang Usaha', 'Hutang Dagang'],
            [
                'kode_akun'           => '2111',
                'nama_akun'           => 'Utang Usaha - Bahan Baku IDR',
                'account_type'        => 'Liability',
                'account_class'       => 'Current - Payable',
                'financial_statement' => 'Balance Sheet',
                'normal_balance'      => 'Credit',
            ]
        );

        if ($coa && $excludeCoaId && (int) $coa->id === (int) $excludeCoaId) {
            return MasterCOA::where('id', '!=', $excludeCoaId)
                ->where(function ($q) {
                    $q->where('kode_akun', 'like', '211%')
                        ->orWhere('nama_akun', 'like', '%Utang%')
                        ->orWhere('nama_akun', 'like', '%Hutang%');
                })
                ->first();
        }

        return $coa;
    }

    public function resolveBebanGajiCoa(?Gaji $gaji = null): ?MasterCOA
    {
        $deptCode = strtoupper((string) ($gaji?->karyawan?->departemen?->kode ?? ''));
        if (in_array($deptCode, ['PRNT', 'SPOTUV', 'SPOTHL', 'PROD', 'FIN'])) {
            $prodCoa = $this->resolveCoa(
                ['5201', '5200'],
                ['Gaji Produksi', 'Tenaga Kerja Langsung']
            );
            if ($prodCoa) {
                return $prodCoa;
            }
        }

        return $this->resolveCoa(
            ['6301', '6201', '5201', '6101'],
            ['Gaji Karyawan Umum', 'Gaji Administrasi', 'Gaji Produksi', 'Beban Gaji'],
            [
                'kode_akun'           => '6301',
                'nama_akun'           => 'Gaji Karyawan Umum',
                'account_type'        => 'Expense',
                'account_class'       => 'Expense - Labor',
                'financial_statement' => 'Income Statement',
                'normal_balance'      => 'Debit',
            ]
        );
    }

    public function resolveDefaultLawanKasCoa(string $tipeTransaksi, ?int $excludeCoaId = null): ?MasterCOA
    {
        if ($tipeTransaksi === 'KAS_MASUK') {
            return $this->resolveCoa(
                ['4540', '3100', '1112'],
                ['Pendapatan Lain-lain', 'Modal Awal Pemilik', 'Bank BCA'],
                [
                    'kode_akun'           => '4540',
                    'nama_akun'           => 'Pendapatan Lain-lain',
                    'account_type'        => 'Revenue',
                    'account_class'       => 'Revenue - Other',
                    'financial_statement' => 'Income Statement',
                    'normal_balance'      => 'Credit',
                ]
            );
        }

        return $this->resolveCoa(
            ['6610', '6206', '1112'],
            ['Biaya Miscellaneous', 'Alat Tulis Kantor', 'Beban Lain-lain'],
            [
                'kode_akun'           => '6610',
                'nama_akun'           => 'Biaya Miscellaneous',
                'account_type'        => 'Expense',
                'account_class'       => 'Expense - Other',
                'financial_statement' => 'Income Statement',
                'normal_balance'      => 'Debit',
            ]
        );
    }
    /*
    |--------------------------------------------------------------------------
    | RUNNING BALANCE & MOVEMENT ENGINE
    |--------------------------------------------------------------------------
    */

    /**
     * Dapatkan saldo berjalan terkini untuk akun COA
     */
    public function getCurrentBalance(int $coaId): float
    {
        $last = CoaMovement::where('coa_id', $coaId)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->first();

        if ($last) {
            return (float) $last->balance_after;
        }

        $coa = MasterCOA::find($coaId);
        return $coa ? ((float) $coa->saldo_debit - (float) $coa->saldo_kredit) : 0.0;
    }

    /**
     * Posting satu baris pergerakan ke buku kas/bank (coa_movements)
     */
    public function postMovement(array $data): CoaMovement
    {
        $coaId = $data['coa_id'] ?? null;
        if (!$coaId) {
            throw ValidationException::withMessages([
                'coa_id' => 'Akun COA wajib ditentukan.',
            ]);
        }

        $nominal = round((float) ($data['nominal'] ?? 0), 2);
        if ($nominal <= 0) {
            throw ValidationException::withMessages([
                'nominal' => 'Nominal mutasi kas/bank harus lebih besar dari 0.',
            ]);
        }

        $movementType = strtoupper($data['movement_type'] ?? 'DEBIT');
        if (!in_array($movementType, ['DEBIT', 'KREDIT'])) {
            throw ValidationException::withMessages([
                'movement_type' => 'Tipe mutasi harus DEBIT atau KREDIT.',
            ]);
        }

        // Lock row MasterCOA untuk memastikan konsistensi urutan saldo berjalan
        MasterCOA::lockForUpdate()->find($coaId);

        $currentBalance = $this->getCurrentBalance($coaId);

        if ($movementType === 'DEBIT') {
            $debit = $nominal;
            $kredit = 0.0;
            $balanceAfter = round($currentBalance + $nominal, 2);
        } else {
            $debit = 0.0;
            $kredit = $nominal;
            $balanceAfter = round($currentBalance - $nominal, 2);
        }

        return CoaMovement::create([
            'coa_id'         => $coaId,
            'tanggal'        => $data['tanggal'] ?? now()->toDateString(),
            'reference_type' => $data['reference_type'],
            'reference_id'   => $data['reference_id'] ?? null,
            'reference_no'   => $data['reference_no'] ?? null,
            'movement_type'  => $movementType,
            'nominal'        => $nominal,
            'debit'          => $debit,
            'kredit'         => $kredit,
            'balance_before' => round($currentBalance, 2),
            'balance_after'  => $balanceAfter,
            'remarks'        => $data['remarks'] ?? null,
            'created_by'     => $data['created_by'] ?? auth()->id(),
        ]);
    }

    /**
     * Hitung ulang saldo berjalan untuk satu akun COA secara berurutan
     */
    public function recalculateRunningBalances(int $coaId): void
    {
        $coa = MasterCOA::lockForUpdate()->find($coaId);
        if (!$coa) {
            return;
        }

        $runningBalance = (float) $coa->saldo_debit - (float) $coa->saldo_kredit;

        $movements = CoaMovement::where('coa_id', $coaId)
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($movements as $m) {
            $before = $runningBalance;
            if ($m->movement_type === 'DEBIT') {
                $runningBalance += (float) $m->nominal;
            } else {
                $runningBalance -= (float) $m->nominal;
            }

            $m->update([
                'balance_before' => round($before, 2),
                'balance_after'  => round($runningBalance, 2),
            ]);
        }
    }

    /**
     * Hapus semua mutasi terkait dokumen referensi tertentu lalu hitung ulang
     */
    public function deleteMovementsFor(string $referenceType, int $referenceId): void
    {
        $movements = CoaMovement::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->get();

        $affectedCoaIds = $movements->pluck('coa_id')->unique()->toArray();

        CoaMovement::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->delete();

        foreach ($affectedCoaIds as $coaId) {
            $this->recalculateRunningBalances($coaId);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | KODE TRANSAKSI GENERATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Generate Kode Transaksi Kas: TRK-Ym-0001
     */
    public function generateKodeKas(?Carbon $date = null): string
    {
        $date = $date ?? now();
        $prefix = 'TRK-' . $date->format('Ym') . '-';

        $last = TransKas::where('kode_transaksi', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($last && preg_match('/-(\d+)$/', $last->kode_transaksi, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $count = TransKas::where('kode_transaksi', 'like', "{$prefix}%")->count();
            $nextNumber = $count + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate Kode Transaksi Bank: BANK-00001
     */
    public function generateKodeBank(?Carbon $date = null): string
    {
        $last = TransKasBank::orderByDesc('id')->first();
        $nextId = $last ? ($last->id + 1) : 1;
        return 'BANK-' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate Kode Transaksi Operasional: OPR-00001
     */
    public function generateKodeOperasional(?Carbon $date = null): string
    {
        $last = OperasionalPay::orderByDesc('id')->first();
        $nextId = $last ? ($last->id + 1) : 1;
        return 'OPR-' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate Kode Jurnal Memorial: JRN-Ym-0001
     */
    public function generateKodeJurnal(?Carbon $date = null): string
    {
        $date = $date ?? now();
        $prefix = 'JRN-' . $date->format('Ym') . '-';

        if (! \Illuminate\Support\Facades\Schema::hasTable('journal_entries')) {
            return $prefix . '0001';
        }

        $last = JournalEntry::where('kode_jurnal', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($last && preg_match('/-(\d+)$/', $last->kode_jurnal, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $count = JournalEntry::where('kode_jurnal', 'like', "{$prefix}%")->count();
            $nextNumber = $count + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /*
    |--------------------------------------------------------------------------
    | TRANSAKSI KAS (TransKas)
    |--------------------------------------------------------------------------
    */

    public function recordKas(array $data): TransKas
    {
        return DB::transaction(function () use ($data) {
            $tipe = $data['tipe_transaksi'] ?? 'KAS_MASUK';
            if (!in_array($tipe, ['KAS_MASUK', 'KAS_KELUAR'])) {
                throw ValidationException::withMessages([
                    'tipe_transaksi' => 'Tipe transaksi harus KAS_MASUK atau KAS_KELUAR.',
                ]);
            }

            $nominal = (float) ($data['nominal'] ?? 0);
            if ($nominal <= 0) {
                throw ValidationException::withMessages([
                    'nominal' => 'Nominal transaksi kas harus lebih besar dari 0.',
                ]);
            }

            $kasId = $data['account_kas_id'] ?? null;
            if (!$kasId) {
                throw ValidationException::withMessages([
                    'account_kas_id' => 'Akun Kas wajib dipilih.',
                ]);
            }

            if (empty($data['account_bank_id']) || (int) $data['account_bank_id'] === (int) $kasId) {
                $defaultLawan = $this->resolveDefaultLawanKasCoa($tipe, (int) $kasId);
                if ($defaultLawan) {
                    $data['account_bank_id'] = $defaultLawan->id;
                }
            }

            if (empty($data['kode_transaksi']) || $data['kode_transaksi'] === 'AUTO') {
                $data['kode_transaksi'] = $this->generateKodeKas(
                    isset($data['tanggal_transaksi']) ? Carbon::parse($data['tanggal_transaksi']) : null
                );
            }

            if (empty($data['status'])) {
                $data['status'] = 'ACTIVE';
            }

            /** @var TransKas $kas */
            $kas = TransKas::create($data);

            $this->syncKasMovements($kas);

            return $kas;
        });
    }

    public function updateKas(TransKas $kas, array $data): TransKas
    {
        return DB::transaction(function () use ($kas, $data) {
            $kas = TransKas::lockForUpdate()->findOrFail($kas->id);

            if (isset($data['nominal'])) {
                $nominal = (float) $data['nominal'];
                if ($nominal <= 0) {
                    throw ValidationException::withMessages([
                        'nominal' => 'Nominal transaksi kas harus lebih besar dari 0.',
                    ]);
                }
            }

            $kas->update($data);

            $this->syncKasMovements($kas);

            return $kas;
        });
    }

    public function deleteKas(TransKas $kas): bool
    {
        return DB::transaction(function () use ($kas) {
            $id = $kas->id;
            $deleted = $kas->delete();
            $this->deleteMovementsFor('TRANS_KAS', $id);
            return (bool) $deleted;
        });
    }

    protected function syncKasMovements(TransKas $kas): void
    {
        // Hapus mutasi lama bila ada
        $this->deleteMovementsFor('TRANS_KAS', $kas->id);

        $nominal = (float) $kas->nominal;
        $tanggal = $kas->tanggal_transaksi ? Carbon::parse($kas->tanggal_transaksi)->toDateString() : now()->toDateString();
        $refNo = $kas->kode_transaksi;
        $desc = $kas->keterangan ?? "Transaksi {$kas->tipe_transaksi} {$refNo}";

        $lawanCoaId = $kas->account_bank_id;
        if (! $lawanCoaId || (int) $lawanCoaId === (int) $kas->account_kas_id) {
            $lawanCoaId = $this->resolveDefaultLawanKasCoa($kas->tipe_transaksi, (int) $kas->account_kas_id)?->id;
        }

        if ($kas->tipe_transaksi === 'KAS_MASUK') {
            // Kas bertambah (DEBIT)
            $this->postMovement([
                'coa_id'         => $kas->account_kas_id,
                'tanggal'        => $tanggal,
                'reference_type' => 'TRANS_KAS',
                'reference_id'   => $kas->id,
                'reference_no'   => $refNo,
                'movement_type'  => 'DEBIT',
                'nominal'        => $nominal,
                'remarks'        => $desc,
            ]);

            // Lawan (Bank/Sumber) bila berbeda akun (KREDIT)
            if ($lawanCoaId && (int) $lawanCoaId !== (int) $kas->account_kas_id) {
                $this->postMovement([
                    'coa_id'         => $lawanCoaId,
                    'tanggal'        => $tanggal,
                    'reference_type' => 'TRANS_KAS',
                    'reference_id'   => $kas->id,
                    'reference_no'   => $refNo,
                    'movement_type'  => 'KREDIT',
                    'nominal'        => $nominal,
                    'remarks'        => "Sumber dana {$desc}",
                ]);
            }
        } else {
            // KAS_KELUAR: Kas berkurang (KREDIT)
            $this->postMovement([
                'coa_id'         => $kas->account_kas_id,
                'tanggal'        => $tanggal,
                'reference_type' => 'TRANS_KAS',
                'reference_id'   => $kas->id,
                'reference_no'   => $refNo,
                'movement_type'  => 'KREDIT',
                'nominal'        => $nominal,
                'remarks'        => $desc,
            ]);

            // Tujuan (Bank/Tujuan/Beban) bila berbeda akun (DEBIT)
            if ($lawanCoaId && (int) $lawanCoaId !== (int) $kas->account_kas_id) {
                $this->postMovement([
                    'coa_id'         => $lawanCoaId,
                    'tanggal'        => $tanggal,
                    'reference_type' => 'TRANS_KAS',
                    'reference_id'   => $kas->id,
                    'reference_no'   => $refNo,
                    'movement_type'  => 'DEBIT',
                    'nominal'        => $nominal,
                    'remarks'        => "Setoran/Pengeluaran ke {$desc}",
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TRANSAKSI BANK (TransKasBank)
    |--------------------------------------------------------------------------
    */

    public function recordBank(array $data): TransKasBank
    {
        return DB::transaction(function () use ($data) {
            $tipe = $data['tipe_transaksi'] ?? 'BANK_MASUK';
            if (!in_array($tipe, ['BANK_MASUK', 'BANK_KELUAR'])) {
                throw ValidationException::withMessages([
                    'tipe_transaksi' => 'Tipe transaksi harus BANK_MASUK atau BANK_KELUAR.',
                ]);
            }

            $nominal = (float) ($data['nominal'] ?? 0);
            if ($nominal <= 0) {
                throw ValidationException::withMessages([
                    'nominal' => 'Nominal transaksi bank harus lebih besar dari 0.',
                ]);
            }

            if (empty($data['account_bank_id'])) {
                throw ValidationException::withMessages([
                    'account_bank_id' => 'Akun Bank wajib dipilih.',
                ]);
            }

            if (empty($data['account_lawan_id'])) {
                throw ValidationException::withMessages([
                    'account_lawan_id' => 'Akun Lawan wajib dipilih.',
                ]);
            }

            if (empty($data['kode_transaksi']) || $data['kode_transaksi'] === 'AUTO') {
                $data['kode_transaksi'] = $this->generateKodeBank(
                    isset($data['tanggal_transaksi']) ? Carbon::parse($data['tanggal_transaksi']) : null
                );
            }

            if (empty($data['status'])) {
                $data['status'] = 'ACTIVE';
            }

            /** @var TransKasBank $bank */
            $bank = TransKasBank::create($data);

            $this->syncBankMovements($bank);

            return $bank;
        });
    }

    public function updateBank(TransKasBank $bank, array $data): TransKasBank
    {
        return DB::transaction(function () use ($bank, $data) {
            $bank = TransKasBank::lockForUpdate()->findOrFail($bank->id);

            if (isset($data['nominal'])) {
                $nominal = (float) $data['nominal'];
                if ($nominal <= 0) {
                    throw ValidationException::withMessages([
                        'nominal' => 'Nominal transaksi bank harus lebih besar dari 0.',
                    ]);
                }
            }

            $bank->update($data);

            $this->syncBankMovements($bank);

            return $bank;
        });
    }

    public function deleteBank(TransKasBank $bank): bool
    {
        return DB::transaction(function () use ($bank) {
            $id = $bank->id;
            $deleted = $bank->delete();
            $this->deleteMovementsFor('TRANS_BANK', $id);
            return (bool) $deleted;
        });
    }

    protected function syncBankMovements(TransKasBank $bank): void
    {
        $this->deleteMovementsFor('TRANS_BANK', $bank->id);

        $nominal = (float) $bank->nominal;
        $tanggal = $bank->tanggal_transaksi ? Carbon::parse($bank->tanggal_transaksi)->toDateString() : now()->toDateString();
        $refNo = $bank->kode_transaksi;
        $desc = $bank->keterangan ?? "Transaksi {$bank->tipe_transaksi} {$refNo}";

        if ($bank->tipe_transaksi === 'BANK_MASUK') {
            // Rekening Bank bertambah (DEBIT)
            $this->postMovement([
                'coa_id'         => $bank->account_bank_id,
                'tanggal'        => $tanggal,
                'reference_type' => 'TRANS_BANK',
                'reference_id'   => $bank->id,
                'reference_no'   => $refNo,
                'movement_type'  => 'DEBIT',
                'nominal'        => $nominal,
                'remarks'        => $desc,
            ]);

            // Rekening Lawan berkurang/sumber (KREDIT)
            if ($bank->account_lawan_id && $bank->account_lawan_id != $bank->account_bank_id) {
                $this->postMovement([
                    'coa_id'         => $bank->account_lawan_id,
                    'tanggal'        => $tanggal,
                    'reference_type' => 'TRANS_BANK',
                    'reference_id'   => $bank->id,
                    'reference_no'   => $refNo,
                    'movement_type'  => 'KREDIT',
                    'nominal'        => $nominal,
                    'remarks'        => "Lawan penerimaan {$desc}",
                ]);
            }
        } else {
            // BANK_KELUAR: Rekening Bank berkurang (KREDIT)
            $this->postMovement([
                'coa_id'         => $bank->account_bank_id,
                'tanggal'        => $tanggal,
                'reference_type' => 'TRANS_BANK',
                'reference_id'   => $bank->id,
                'reference_no'   => $refNo,
                'movement_type'  => 'KREDIT',
                'nominal'        => $nominal,
                'remarks'        => $desc,
            ]);

            // Rekening Lawan pengeluaran/beban (DEBIT)
            if ($bank->account_lawan_id && $bank->account_lawan_id != $bank->account_bank_id) {
                $this->postMovement([
                    'coa_id'         => $bank->account_lawan_id,
                    'tanggal'        => $tanggal,
                    'reference_type' => 'TRANS_BANK',
                    'reference_id'   => $bank->id,
                    'reference_no'   => $refNo,
                    'movement_type'  => 'DEBIT',
                    'nominal'        => $nominal,
                    'remarks'        => "Lawan pengeluaran {$desc}",
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TRANSAKSI OPERASIONAL (OperasionalPay)
    |--------------------------------------------------------------------------
    */

    public function recordOperasional(array $data): OperasionalPay
    {
        return DB::transaction(function () use ($data) {
            $nominal = (float) ($data['nominal'] ?? 0);
            if ($nominal <= 0) {
                throw ValidationException::withMessages([
                    'nominal' => 'Nominal biaya operasional harus lebih besar dari 0.',
                ]);
            }

            if (empty($data['account_kas_id'])) {
                throw ValidationException::withMessages([
                    'account_kas_id' => 'Akun Kas pembayaran wajib dipilih.',
                ]);
            }

            if (empty($data['account_beban_id'])) {
                throw ValidationException::withMessages([
                    'account_beban_id' => 'Akun Beban operasional wajib dipilih.',
                ]);
            }

            if (empty($data['kode_transaksi']) || $data['kode_transaksi'] === 'AUTO') {
                $data['kode_transaksi'] = $this->generateKodeOperasional(
                    isset($data['tanggal_transaksi']) ? Carbon::parse($data['tanggal_transaksi']) : null
                );
            }

            if (empty($data['status'])) {
                $data['status'] = 'ACTIVE';
            }

            /** @var OperasionalPay $op */
            $op = OperasionalPay::create($data);

            $this->syncOperasionalMovements($op);

            return $op;
        });
    }

    public function updateOperasional(OperasionalPay $op, array $data): OperasionalPay
    {
        return DB::transaction(function () use ($op, $data) {
            $op = OperasionalPay::lockForUpdate()->findOrFail($op->id);

            if (isset($data['nominal'])) {
                $nominal = (float) $data['nominal'];
                if ($nominal <= 0) {
                    throw ValidationException::withMessages([
                        'nominal' => 'Nominal biaya operasional harus lebih besar dari 0.',
                    ]);
                }
            }

            $op->update($data);

            $this->syncOperasionalMovements($op);

            return $op;
        });
    }

    public function deleteOperasional(OperasionalPay $op): bool
    {
        return DB::transaction(function () use ($op) {
            $id = $op->id;
            $deleted = $op->delete();
            $this->deleteMovementsFor('OPERASIONAL_PAY', $id);
            return (bool) $deleted;
        });
    }

    protected function syncOperasionalMovements(OperasionalPay $op): void
    {
        $this->deleteMovementsFor('OPERASIONAL_PAY', $op->id);

        $nominal = (float) $op->nominal;
        $tanggal = $op->tanggal_transaksi ? Carbon::parse($op->tanggal_transaksi)->toDateString() : now()->toDateString();
        $refNo = $op->kode_transaksi;
        $desc = $op->keterangan ?? "Biaya Operasional {$refNo}";

        // Kas berkurang (KREDIT)
        $this->postMovement([
            'coa_id'         => $op->account_kas_id,
            'tanggal'        => $tanggal,
            'reference_type' => 'OPERASIONAL_PAY',
            'reference_id'   => $op->id,
            'reference_no'   => $refNo,
            'movement_type'  => 'KREDIT',
            'nominal'        => $nominal,
            'remarks'        => "Pengeluaran Kas: {$desc}",
        ]);

        // Beban bertambah (DEBIT)
        if ($op->account_beban_id && $op->account_beban_id != $op->account_kas_id) {
            $this->postMovement([
                'coa_id'         => $op->account_beban_id,
                'tanggal'        => $tanggal,
                'reference_type' => 'OPERASIONAL_PAY',
                'reference_id'   => $op->id,
                'reference_no'   => $refNo,
                'movement_type'  => 'DEBIT',
                'nominal'        => $nominal,
                'remarks'        => "Beban: {$desc}",
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | AR INVOICE ACCRUAL HOOKS (Invoice)
    |--------------------------------------------------------------------------
    */

    public function postInvoice(Invoice $invoice): void
    {
        if (in_array($invoice->status, ['Draft', 'Cancelled'], true)) {
            return;
        }

        $grandTotal = round((float) ($invoice->grand_total ?? 0), 2);
        if ($grandTotal <= 0) {
            return;
        }

        $refNo = $invoice->kode_invoice ?? 'INV-' . $invoice->id;
        $tanggal = $invoice->tanggal_invoice
            ? Carbon::parse($invoice->tanggal_invoice)->toDateString()
            : now()->toDateString();
        $namaCustomer = $invoice->customer?->nama_customer ?? 'Customer';

        $subtotal = (float) ($invoice->subtotal ?? 0);
        $ppnPersen = max(0.0, (float) ($invoice->ppn ?? 0));
        $ppnNominal = round(($subtotal * $ppnPersen) / 100, 2);
        if ($ppnNominal >= $grandTotal) {
            $ppnNominal = 0.0;
        }
        $pendapatanNominal = round($grandTotal - $ppnNominal, 2);

        $piutangCoa = $this->resolvePiutangCoa();
        $pendapatanCoa = $this->resolvePendapatanPenjualanCoa();
        $ppnKeluaranCoa = $ppnNominal > 0 ? $this->resolvePpnKeluaranCoa() : null;

        if (! $piutangCoa || ! $pendapatanCoa) {
            return;
        }

        // 1. DEBIT: Piutang Usaha bertambah sebesar Grand Total Tagihan
        $this->postMovement([
            'coa_id'         => $piutangCoa->id,
            'tanggal'        => $tanggal,
            'reference_type' => 'INVOICE',
            'reference_id'   => $invoice->id,
            'reference_no'   => $refNo,
            'movement_type'  => 'DEBIT',
            'nominal'        => $grandTotal,
            'remarks'        => "Pengakuan Piutang Penjualan Invoice {$refNo} ({$namaCustomer})",
        ]);

        // 2. KREDIT: Pendapatan Penjualan (DPP + Ongkir - Diskon)
        if ($ppnKeluaranCoa && $ppnNominal > 0 && $pendapatanNominal > 0) {
            $this->postMovement([
                'coa_id'         => $pendapatanCoa->id,
                'tanggal'        => $tanggal,
                'reference_type' => 'INVOICE',
                'reference_id'   => $invoice->id,
                'reference_no'   => $refNo,
                'movement_type'  => 'KREDIT',
                'nominal'        => $pendapatanNominal,
                'remarks'        => "Pendapatan Penjualan Invoice {$refNo} ({$namaCustomer})",
            ]);

            $this->postMovement([
                'coa_id'         => $ppnKeluaranCoa->id,
                'tanggal'        => $tanggal,
                'reference_type' => 'INVOICE',
                'reference_id'   => $invoice->id,
                'reference_no'   => $refNo,
                'movement_type'  => 'KREDIT',
                'nominal'        => $ppnNominal,
                'remarks'        => "PPN Keluaran Invoice {$refNo} ({$namaCustomer})",
            ]);
        } else {
            $this->postMovement([
                'coa_id'         => $pendapatanCoa->id,
                'tanggal'        => $tanggal,
                'reference_type' => 'INVOICE',
                'reference_id'   => $invoice->id,
                'reference_no'   => $refNo,
                'movement_type'  => 'KREDIT',
                'nominal'        => $grandTotal,
                'remarks'        => "Pendapatan Penjualan Invoice {$refNo} ({$namaCustomer})",
            ]);
        }
    }

    public function syncInvoice(Invoice $invoice): void
    {
        $this->deleteMovementsFor('INVOICE', $invoice->id);
        $this->postInvoice($invoice);
    }

    public function deleteInvoice(Invoice $invoice): void
    {
        $this->deleteMovementsFor('INVOICE', $invoice->id);
    }

    /*
    |--------------------------------------------------------------------------
    | AR INVOICE PAYMENT HOOKS (InvPayment)
    |--------------------------------------------------------------------------
    */

    public function postInvPayment(InvPayment $payment): void
    {
        // Hanya pembayaran berhasil yang boleh menambah mutasi kas/bank.
        if (strtoupper((string) $payment->status) !== 'SUCCESS') {
            return;
        }
        if (empty($payment->coa_id)) {
            return;
        }

        $nominal = (float) $payment->nominal_bayar;
        if ($nominal <= 0) {
            return;
        }

        $invoice = $payment->invoice;
        $refNo = $invoice?->kode_invoice ?? 'INV-' . $payment->invoice_id;
        $tanggal = $payment->tanggal_bayar ? Carbon::parse($payment->tanggal_bayar)->toDateString() : now()->toDateString();
        $desc = "Penerimaan Pembayaran Customer Invoice {$refNo}";

        // 1. DEBIT: Kas/Bank bertambah
        $this->postMovement([
            'coa_id'         => $payment->coa_id,
            'tanggal'        => $tanggal,
            'reference_type' => 'INV_PAYMENT',
            'reference_id'   => $payment->id,
            'reference_no'   => $refNo,
            'movement_type'  => 'DEBIT',
            'nominal'        => $nominal,
            'remarks'        => $desc,
        ]);

        // 2. KREDIT: Piutang Usaha berkurang (Prinsip Double-Entry Indigama)
        $piutangCoa = $this->resolvePiutangCoa((int) $payment->coa_id);

        if ($piutangCoa && (int) $piutangCoa->id !== (int) $payment->coa_id) {
            $this->postMovement([
                'coa_id'         => $piutangCoa->id,
                'tanggal'        => $tanggal,
                'reference_type' => 'INV_PAYMENT',
                'reference_id'   => $payment->id,
                'reference_no'   => $refNo,
                'movement_type'  => 'KREDIT',
                'nominal'        => $nominal,
                'remarks'        => "Pelunasan Piutang Invoice {$refNo}",
            ]);
        }
    }

    public function syncInvPayment(InvPayment $payment): void
    {
        $this->deleteMovementsFor('INV_PAYMENT', $payment->id);
        $this->postInvPayment($payment);
    }

    public function deleteInvPayment(InvPayment $payment): void
    {
        $this->deleteMovementsFor('INV_PAYMENT', $payment->id);
    }

    /*
    |--------------------------------------------------------------------------
    | AP SUPPLIER BILLING ACCRUAL HOOKS (PoBilling)
    |--------------------------------------------------------------------------
    */

    public function postPoBilling(PoBilling $billing): void
    {
        $grandTotal = round((float) ($billing->grand_total ?? 0), 2);
        if ($grandTotal <= 0) {
            return;
        }

        $refNo = $billing->kode_tagihan ?? 'BILL-' . $billing->id;
        $tanggal = $billing->tanggal_transaksi
            ? Carbon::parse($billing->tanggal_transaksi)->toDateString()
            : now()->toDateString();
        $namaSupplier = $billing->supplier?->nama_supplier ?? 'Supplier';

        $ppnNominal = max(0.0, round((float) ($billing->ppn ?? 0), 2));
        if ($ppnNominal >= $grandTotal) {
            $ppnNominal = 0.0;
        }
        $dppNominal = round($grandTotal - $ppnNominal, 2);

        $persediaanCoa = $this->resolvePersediaanBahanCoa();
        $ppnMasukanCoa = $ppnNominal > 0 ? $this->resolvePpnMasukanCoa() : null;
        $hutangCoa = $this->resolveHutangUsahaCoa();

        if (! $persediaanCoa || ! $hutangCoa) {
            return;
        }

        // 1. DEBIT: Persediaan Bahan Baku (+ PPN Masukan bila ada)
        if ($ppnMasukanCoa && $ppnNominal > 0 && $dppNominal > 0) {
            $this->postMovement([
                'coa_id'         => $persediaanCoa->id,
                'tanggal'        => $tanggal,
                'reference_type' => 'PO_BILLING',
                'reference_id'   => $billing->id,
                'reference_no'   => $refNo,
                'movement_type'  => 'DEBIT',
                'nominal'        => $dppNominal,
                'remarks'        => "Pembelian / Persediaan Tagihan {$refNo} ({$namaSupplier})",
            ]);

            $this->postMovement([
                'coa_id'         => $ppnMasukanCoa->id,
                'tanggal'        => $tanggal,
                'reference_type' => 'PO_BILLING',
                'reference_id'   => $billing->id,
                'reference_no'   => $refNo,
                'movement_type'  => 'DEBIT',
                'nominal'        => $ppnNominal,
                'remarks'        => "PPN Masukan Tagihan {$refNo} ({$namaSupplier})",
            ]);
        } else {
            $this->postMovement([
                'coa_id'         => $persediaanCoa->id,
                'tanggal'        => $tanggal,
                'reference_type' => 'PO_BILLING',
                'reference_id'   => $billing->id,
                'reference_no'   => $refNo,
                'movement_type'  => 'DEBIT',
                'nominal'        => $grandTotal,
                'remarks'        => "Pembelian / Persediaan Tagihan {$refNo} ({$namaSupplier})",
            ]);
        }

        // 2. KREDIT: Utang Usaha bertambah
        $this->postMovement([
            'coa_id'         => $hutangCoa->id,
            'tanggal'        => $tanggal,
            'reference_type' => 'PO_BILLING',
            'reference_id'   => $billing->id,
            'reference_no'   => $refNo,
            'movement_type'  => 'KREDIT',
            'nominal'        => $grandTotal,
            'remarks'        => "Pengakuan Hutang Usaha Tagihan {$refNo} ({$namaSupplier})",
        ]);
    }

    public function syncPoBilling(PoBilling $billing): void
    {
        $this->deleteMovementsFor('PO_BILLING', $billing->id);
        $this->postPoBilling($billing);
    }

    public function deletePoBilling(PoBilling $billing): void
    {
        $this->deleteMovementsFor('PO_BILLING', $billing->id);
    }

    /*
    |--------------------------------------------------------------------------
    | AP SUPPLIER PAYMENT HOOKS (PoBillPay)
    |--------------------------------------------------------------------------
    */

    public function postPoBillPay(PoBillPay $payment): void
    {
        $refNo = $payment->kode_pembayaran;
        $tanggal = $payment->tanggal_pembayaran ? Carbon::parse($payment->tanggal_pembayaran)->toDateString() : now()->toDateString();
        $billing = $payment->poBilling;
        $billNo = $billing?->kode_tagihan ?? 'BILL-' . $payment->po_billing_id;

        foreach ($payment->items as $item) {
            $nominal = (float) $item->nominal_pembayaran;
            if ($nominal <= 0) {
                continue;
            }

            // 1. KREDIT: Kas/Bank berkurang membayar supplier
            if ($item->coa_kredit_id) {
                $this->postMovement([
                    'coa_id'         => $item->coa_kredit_id,
                    'tanggal'        => $tanggal,
                    'reference_type' => 'PO_BILL_PAY',
                    'reference_id'   => $payment->id,
                    'reference_no'   => $refNo,
                    'movement_type'  => 'KREDIT',
                    'nominal'        => $nominal,
                    'remarks'        => "Pembayaran Tagihan Supplier {$billNo} ({$refNo})",
                ]);
            }

            // 2. DEBIT: Hutang dagang berkurang (fallback otomatis ke Utang Usaha 2111 bila kosong)
            $debitCoaId = $item->coa_debit_id;
            if (! $debitCoaId || (int) $debitCoaId === (int) $item->coa_kredit_id) {
                $debitCoaId = $this->resolveHutangUsahaCoa((int) $item->coa_kredit_id)?->id;
            }

            if ($debitCoaId && (int) $debitCoaId !== (int) $item->coa_kredit_id) {
                $this->postMovement([
                    'coa_id'         => $debitCoaId,
                    'tanggal'        => $tanggal,
                    'reference_type' => 'PO_BILL_PAY',
                    'reference_id'   => $payment->id,
                    'reference_no'   => $refNo,
                    'movement_type'  => 'DEBIT',
                    'nominal'        => $nominal,
                    'remarks'        => "Pelunasan Hutang Supplier {$billNo} ({$refNo})",
                ]);
            }
        }
    }

    public function syncPoBillPay(PoBillPay $payment): void
    {
        $this->deleteMovementsFor('PO_BILL_PAY', $payment->id);
        $this->postPoBillPay($payment);
    }

    public function deletePoBillPay(PoBillPay $payment): void
    {
        $this->deleteMovementsFor('PO_BILL_PAY', $payment->id);
    }

    /*
    |--------------------------------------------------------------------------
    | PAYROLL HOOKS (Gaji)
    |--------------------------------------------------------------------------
    */

    public function postPayroll(\App\Models\Gaji $gaji): void
    {
        if (empty($gaji->account_kas_id) || $gaji->status !== 'Paid') {
            return;
        }

        $nominal = (float) $gaji->gaji_bersih;
        if ($nominal <= 0) {
            return;
        }

        $refNo = $gaji->kode_slip;
        $tanggal = $gaji->tanggal_pembayaran ? Carbon::parse($gaji->tanggal_pembayaran)->toDateString() : now()->toDateString();
        $namaKaryawan = $gaji->karyawan?->nama ?? "Karyawan #{$gaji->karyawan_id}";
        $desc = "Pembayaran Gaji Karyawan {$namaKaryawan} Periode {$gaji->periode} ({$refNo})";

        // 1. KREDIT: Kas/Bank berkurang
        $this->postMovement([
            'coa_id'         => $gaji->account_kas_id,
            'tanggal'        => $tanggal,
            'reference_type' => 'PAYROLL',
            'reference_id'   => $gaji->id,
            'reference_no'   => $refNo,
            'movement_type'  => 'KREDIT',
            'nominal'        => $nominal,
            'remarks'        => $desc,
        ]);

        // 2. DEBIT: Beban Gaji bertambah (Double-Entry)
        $bebanGajiCoa = $this->resolveBebanGajiCoa($gaji);
        if ($bebanGajiCoa && (int) $bebanGajiCoa->id !== (int) $gaji->account_kas_id) {
            $this->postMovement([
                'coa_id'         => $bebanGajiCoa->id,
                'tanggal'        => $tanggal,
                'reference_type' => 'PAYROLL',
                'reference_id'   => $gaji->id,
                'reference_no'   => $refNo,
                'movement_type'  => 'DEBIT',
                'nominal'        => $nominal,
                'remarks'        => "Beban Gaji Karyawan {$namaKaryawan} Periode {$gaji->periode} ({$refNo})",
            ]);
        }
    }

    public function syncPayroll(\App\Models\Gaji $gaji): void
    {
        $this->deleteMovementsFor('PAYROLL', $gaji->id);
        if ($gaji->status === 'Paid') {
            $this->postPayroll($gaji);
        }
    }

    public function deletePayroll(\App\Models\Gaji $gaji): void
    {
        $this->deleteMovementsFor('PAYROLL', $gaji->id);
    }

    /*
    |--------------------------------------------------------------------------
    | JURNAL MEMORIAL / PENYESUAIAN (JournalEntry - Adopsi TranMemorial Indigama)
    |--------------------------------------------------------------------------
    */

    /**
     * Validasi dan hitung total Debit & Kredit dari rincian baris jurnal
     */
    protected function validateAndSummarizeJournalLines(array $lines): array
    {
        if (count($lines) < 2) {
            throw ValidationException::withMessages([
                'lines' => 'Jurnal Memorial minimal harus terdiri dari 2 baris akun (pasangan Debit dan Kredit).',
            ]);
        }

        $totalDebit = 0.0;
        $totalKredit = 0.0;
        $cleanLines = [];

        foreach ($lines as $idx => $line) {
            $coaId = $line['coa_id'] ?? null;
            if (! $coaId) {
                throw ValidationException::withMessages([
                    "lines.{$idx}.coa_id" => 'Akun COA pada setiap baris wajib dipilih.',
                ]);
            }

            $debit = max(0.0, round((float) ($line['debit'] ?? 0), 2));
            $kredit = max(0.0, round((float) ($line['kredit'] ?? 0), 2));

            if ($debit <= 0 && $kredit <= 0) {
                throw ValidationException::withMessages([
                    'lines' => 'Setiap baris jurnal harus memiliki nominal Debit atau Kredit lebih dari 0.',
                ]);
            }

            if ($debit > 0 && $kredit > 0) {
                throw ValidationException::withMessages([
                    'lines' => 'Satu baris jurnal tidak boleh mengisi kolom Debit dan Kredit sekaligus.',
                ]);
            }

            $totalDebit += $debit;
            $totalKredit += $kredit;

            $cleanLines[] = [
                'coa_id'     => (int) $coaId,
                'keterangan' => $line['keterangan'] ?? null,
                'debit'      => $debit,
                'kredit'     => $kredit,
            ];
        }

        $totalDebit = round($totalDebit, 2);
        $totalKredit = round($totalKredit, 2);

        if (abs($totalDebit - $totalKredit) >= 0.01) {
            $selisih = abs($totalDebit - $totalKredit);
            throw ValidationException::withMessages([
                'lines' => sprintf(
                    'Jurnal tidak seimbang (Unbalanced)! Total Debit (Rp %s) harus sama persis dengan Total Kredit (Rp %s). Selisih: Rp %s.',
                    number_format($totalDebit, 0, ',', '.'),
                    number_format($totalKredit, 0, ',', '.'),
                    number_format($selisih, 0, ',', '.')
                ),
            ]);
        }

        return [
            'total_debit'  => $totalDebit,
            'total_kredit' => $totalKredit,
            'lines'        => $cleanLines,
        ];
    }

    public function recordJournalMemorial(array $data): JournalEntry
    {
        return DB::transaction(function () use ($data) {
            $linesInput = $data['lines'] ?? [];
            unset($data['lines']);

            $summary = $this->validateAndSummarizeJournalLines($linesInput);

            if (empty($data['kode_jurnal']) || $data['kode_jurnal'] === 'AUTO') {
                $data['kode_jurnal'] = $this->generateKodeJurnal(
                    isset($data['tanggal']) ? Carbon::parse($data['tanggal']) : null
                );
            }

            if (empty($data['tanggal'])) {
                $data['tanggal'] = now()->toDateString();
            }

            if (empty($data['periode'])) {
                $data['periode'] = Carbon::parse($data['tanggal'])->format('F Y');
            }

            if (empty($data['status'])) {
                $data['status'] = 'POSTED';
            }

            $data['total_debit'] = $summary['total_debit'];
            $data['total_kredit'] = $summary['total_kredit'];
            $data['created_by'] = $data['created_by'] ?? auth()->id();

            /** @var JournalEntry $journal */
            $journal = JournalEntry::create($data);

            foreach ($summary['lines'] as $line) {
                $journal->lines()->create($line);
            }

            $journal->load('lines');
            $this->syncJournalMemorialMovements($journal);

            return $journal;
        });
    }

    public function updateJournalMemorial(JournalEntry $journal, array $data): JournalEntry
    {
        return DB::transaction(function () use ($journal, $data) {
            $journal = JournalEntry::lockForUpdate()->findOrFail($journal->id);

            $linesInput = $data['lines'] ?? null;
            unset($data['lines']);

            if ($linesInput !== null) {
                $summary = $this->validateAndSummarizeJournalLines($linesInput);
                $data['total_debit'] = $summary['total_debit'];
                $data['total_kredit'] = $summary['total_kredit'];

                $journal->update($data);
                $journal->lines()->delete();

                foreach ($summary['lines'] as $line) {
                    $journal->lines()->create($line);
                }
            } else {
                $journal->update($data);
            }

            $journal->load('lines');
            $this->syncJournalMemorialMovements($journal);

            return $journal;
        });
    }

    public function deleteJournalMemorial(JournalEntry $journal): bool
    {
        return DB::transaction(function () use ($journal) {
            $id = $journal->id;
            $journal->lines()->delete();
            $deleted = $journal->delete();
            $this->deleteMovementsFor('JOURNAL_MEMORIAL', $id);
            return (bool) $deleted;
        });
    }

    public function syncJournalMemorialMovements(JournalEntry $journal): void
    {
        $this->deleteMovementsFor('JOURNAL_MEMORIAL', $journal->id);

        if (strtoupper((string) $journal->status) !== 'POSTED') {
            return;
        }

        $journal->loadMissing('lines');
        $tanggal = $journal->tanggal ? Carbon::parse($journal->tanggal)->toDateString() : now()->toDateString();
        $refNo = $journal->kode_jurnal;
        $headerDesc = $journal->keterangan ?: "Jurnal {$journal->jenis_jurnal} {$refNo}";

        foreach ($journal->lines as $line) {
            $debit = (float) $line->debit;
            $kredit = (float) $line->kredit;
            $remarks = $line->keterangan ? "{$headerDesc} - {$line->keterangan}" : $headerDesc;

            if ($debit > 0) {
                $this->postMovement([
                    'coa_id'         => $line->coa_id,
                    'tanggal'        => $tanggal,
                    'reference_type' => 'JOURNAL_MEMORIAL',
                    'reference_id'   => $journal->id,
                    'reference_no'   => $refNo,
                    'movement_type'  => 'DEBIT',
                    'nominal'        => $debit,
                    'remarks'        => $remarks,
                    'created_by'     => $journal->created_by ?? auth()->id(),
                ]);
            } elseif ($kredit > 0) {
                $this->postMovement([
                    'coa_id'         => $line->coa_id,
                    'tanggal'        => $tanggal,
                    'reference_type' => 'JOURNAL_MEMORIAL',
                    'reference_id'   => $journal->id,
                    'reference_no'   => $refNo,
                    'movement_type'  => 'KREDIT',
                    'nominal'        => $kredit,
                    'remarks'        => $remarks,
                    'created_by'     => $journal->created_by ?? auth()->id(),
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MASTER COA GUARDS
    |--------------------------------------------------------------------------
    */

    public function canDeleteCoa(MasterCOA $coa): bool
    {
        if ($coa->movements()->exists()) {
            return false;
        }

        if (TransKas::where('account_kas_id', $coa->id)->orWhere('account_bank_id', $coa->id)->exists()) {
            return false;
        }

        if (TransKasBank::where('account_bank_id', $coa->id)->orWhere('account_lawan_id', $coa->id)->exists()) {
            return false;
        }

        if (OperasionalPay::where('account_kas_id', $coa->id)->orWhere('account_beban_id', $coa->id)->exists()) {
            return false;
        }

        if (\App\Models\Gaji::where('account_kas_id', $coa->id)->exists()) {
            return false;
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('journal_entry_lines') && JournalEntryLine::where('coa_id', $coa->id)->exists()) {
            return false;
        }

        return true;
    }
}
