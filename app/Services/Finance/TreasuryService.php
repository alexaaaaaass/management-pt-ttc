<?php

namespace App\Services\Finance;

use App\Models\CoaMovement;
use App\Models\InvPayment;
use App\Models\MasterCOA;
use App\Models\OperasionalPay;
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
            if ($kas->account_bank_id && $kas->account_bank_id != $kas->account_kas_id) {
                $this->postMovement([
                    'coa_id'         => $kas->account_bank_id,
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

            // Tujuan (Bank/Tujuan) bila berbeda akun (DEBIT)
            if ($kas->account_bank_id && $kas->account_bank_id != $kas->account_kas_id) {
                $this->postMovement([
                    'coa_id'         => $kas->account_bank_id,
                    'tanggal'        => $tanggal,
                    'reference_type' => 'TRANS_KAS',
                    'reference_id'   => $kas->id,
                    'reference_no'   => $refNo,
                    'movement_type'  => 'DEBIT',
                    'nominal'        => $nominal,
                    'remarks'        => "Setoran ke {$desc}",
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

        $this->postMovement([
            'coa_id'         => $payment->coa_id,
            'tanggal'        => $tanggal,
            'reference_type' => 'INV_PAYMENT',
            'reference_id'   => $payment->id,
            'reference_no'   => $refNo,
            'movement_type'  => 'DEBIT', // Kas/Bank bertambah
            'nominal'        => $nominal,
            'remarks'        => $desc,
        ]);
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

            // Kas/Bank berkurang membayar supplier (KREDIT)
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

            // Hutang dagang berkurang (DEBIT) bila akun debit ditentukan
            if ($item->coa_debit_id && $item->coa_debit_id != $item->coa_kredit_id) {
                $this->postMovement([
                    'coa_id'         => $item->coa_debit_id,
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

        // Kas/Bank berkurang (KREDIT)
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

        return true;
    }
}
