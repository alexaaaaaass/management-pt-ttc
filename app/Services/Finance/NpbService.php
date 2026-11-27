<?php

namespace App\Services\Finance;

use App\Models\CoaMovement;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\MasterCOA;
use App\Models\NpbRequest;
use App\Models\NpbSettlement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NpbService
{
    public const ROMAN_MONTHS = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
        7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
    ];

    public function generateNomorNpb(?Carbon $date = null, string $dept = 'KEU'): string
    {
        $d = $date ?? now();
        $deptClean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $dept) ?: 'KEU');
        $roman = self::ROMAN_MONTHS[(int) $d->format('n')] ?? 'I';
        $year = $d->format('Y');
        $base = "NPB/{$deptClean}/{$roman}/{$year}";

        if (! NpbRequest::where('nomor_pengajuan', $base)->exists()) {
            return $base;
        }

        $seq = 2;
        while (NpbRequest::where('nomor_pengajuan', sprintf('%s-%02d', $base, $seq))->exists()) {
            $seq++;
        }

        return sprintf('%s-%02d', $base, $seq);
    }

    public function generateNomorSettlement(?Carbon $date = null, string $dept = 'KEU'): string
    {
        $d = $date ?? now();
        $deptClean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $dept) ?: 'KEU');
        $roman = self::ROMAN_MONTHS[(int) $d->format('n')] ?? 'I';
        $year = $d->format('Y');
        $base = "LPJ-NPB/{$deptClean}/{$roman}/{$year}";

        if (! NpbSettlement::where('nomor_penyelesaian', $base)->exists()) {
            return $base;
        }

        $seq = 2;
        while (NpbSettlement::where('nomor_penyelesaian', sprintf('%s-%02d', $base, $seq))->exists()) {
            $seq++;
        }

        return sprintf('%s-%02d', $base, $seq);
    }

    /**
     * Ambil saldo posisi kas & bank aktif dari Buku Besar per tanggal cut-off.
     */
    public function getDefaultFundPositionsFromLedger(?string $asOfDate = null): array
    {
        $end = $asOfDate ? Carbon::parse($asOfDate)->toDateString() : now()->toDateString();

        $presets = [
            ['code' => '1114', 'label' => 'BRI Giro'],
            ['code' => '1111', 'label' => 'BRI Tabungan'],
            ['code' => '1112', 'label' => 'BCA Giro'],
            ['code' => null,   'label' => 'BCA Priska'],
            ['code' => '1116', 'label' => 'Petty Cash Juan'],
            ['code' => null,   'label' => 'Petty Cash Yaul'],
        ];

        $coasByCode = MasterCOA::whereIn('kode_akun', ['1111', '1112', '1113', '1114', '1116'])
            ->get()
            ->keyBy(fn ($c) => trim((string) $c->kode_akun));

        $coaIds = $coasByCode->pluck('id')->all();
        $sums = empty($coaIds)
            ? collect()
            : CoaMovement::whereIn('coa_id', $coaIds)
                ->whereDate('tanggal', '<=', $end)
                ->selectRaw('coa_id, COALESCE(SUM(debit),0) as d, COALESCE(SUM(kredit),0) as k')
                ->groupBy('coa_id')
                ->get()
                ->keyBy('coa_id');

        $rows = [];
        $urutan = 1;
        foreach ($presets as $p) {
            $coa = $p['code'] ? $coasByCode->get($p['code']) : null;
            $saldo = 0.0;
            if ($coa) {
                $m = $sums->get($coa->id);
                $open = (float) ($coa->saldo_debit ?? 0) - (float) ($coa->saldo_kredit ?? 0);
                $mut = (float) ($m->d ?? 0) - (float) ($m->k ?? 0);
                $saldo = round($open + $mut, 2);
            }

            $rows[] = [
                'urutan'        => $urutan++,
                'coa_id'        => $coa?->id,
                'nama_rekening' => $p['label'],
                'saldo'         => $saldo,
                'keterangan'    => $coa ? "Auto saldo COA {$p['code']}" : null,
            ];
        }

        return $rows;
    }

    /**
     * Template pos kebutuhan rutin bulanan standar PT TTC.
     */
    public function getDefaultMonthlyTemplateItems(): array
    {
        $coaMap = MasterCOA::whereIn('kode_akun', ['1211', '6301', '6502', '1231', '6203', '6610', '6207'])
            ->get()
            ->keyBy(fn ($c) => trim((string) $c->kode_akun));

        $cid = fn (string $code) => $coaMap->get($code)?->id;

        return [
            ['urutan' => 1, 'kategori' => 'A_KEWAJIBAN_TETAP',      'uraian' => 'Tagihan Gudang B8-B9', 'karakter' => 'Bulanan', 'coa_id' => $cid('1211'), 'nominal_utama' => 188437500, 'nominal_proyeksi' => 188437500],
            ['urutan' => 2, 'kategori' => 'A_KEWAJIBAN_TETAP',      'uraian' => 'Gaji Karyawan',        'karakter' => 'Bulanan', 'coa_id' => $cid('6301'), 'nominal_utama' => 19852000,  'nominal_proyeksi' => 19852000],
            ['urutan' => 3, 'kategori' => 'A_KEWAJIBAN_TETAP',      'uraian' => 'BPJS Ketenagakerjaan', 'karakter' => 'Bulanan', 'coa_id' => $cid('6502'), 'nominal_utama' => 1113652,   'nominal_proyeksi' => 1113652],
            ['urutan' => 4, 'kategori' => 'A_KEWAJIBAN_TETAP',      'uraian' => 'Cicilan Kendaraan',    'karakter' => 'Bulanan', 'coa_id' => $cid('1231'), 'nominal_utama' => 53074000,  'nominal_proyeksi' => 53074000],
            ['urutan' => 5, 'kategori' => 'B_UTILITAS_OPERASIONAL', 'uraian' => 'PDAM',                 'karakter' => 'Bulanan', 'coa_id' => $cid('6203'), 'nominal_utama' => 130000,    'nominal_proyeksi' => 130000],
            ['urutan' => 6, 'kategori' => 'B_UTILITAS_OPERASIONAL', 'uraian' => 'Operasional Gudang',   'karakter' => 'Bulanan', 'coa_id' => $cid('6610'), 'nominal_utama' => 500000,    'nominal_proyeksi' => 500000],
            ['urutan' => 7, 'kategori' => 'B_UTILITAS_OPERASIONAL', 'uraian' => 'Listrik Trafo',        'karakter' => 'Bulanan', 'coa_id' => $cid('6203'), 'nominal_utama' => 23684145,  'nominal_proyeksi' => 23684145],
            ['urutan' => 8, 'kategori' => 'B_UTILITAS_OPERASIONAL', 'uraian' => 'Wifi',                 'karakter' => 'Bulanan', 'coa_id' => $cid('6207'), 'nominal_utama' => 1254300,   'nominal_proyeksi' => 1254300],
        ];
    }

    /**
     * Hitung ulang seluruh total & kekurangan pendanaan pada NPB.
     */
    public function recalculateRequest(NpbRequest $npb): NpbRequest
    {
        $npb->load(['fundPositions', 'items']);

        $totalUtama = round((float) $npb->items->sum('nominal_utama'), 2);
        $totalProyeksi = round((float) $npb->items->sum('nominal_proyeksi'), 2);
        $totalGabungan = round($totalUtama + $totalProyeksi, 2);

        $totalSaldo = round((float) $npb->fundPositions->sum('saldo'), 2);
        $danaTersedia = $totalSaldo;
        $kekurangan = round($danaTersedia - $totalUtama, 2);

        $npb->updateQuietly([
            'total_kebutuhan_utama'    => $totalUtama,
            'total_kebutuhan_proyeksi' => $totalProyeksi,
            'total_kebutuhan_gabungan' => $totalGabungan,
            'total_saldo_sebelumnya'   => $totalSaldo,
            'total_dana_tersedia'      => $danaTersedia,
            'kekurangan_pendanaan'     => $kekurangan,
        ]);

        return $npb->refresh();
    }

    /**
     * Susun array item awal Penyelesaian NPB (LPJ) dari rincian NPB terpilih.
     */
    public function buildSettlementItemsFromNpb(NpbRequest $npb, string $periodeRealisasi = 'UTAMA'): array
    {
        $npb->loadMissing('items');
        $defaultBankId = MasterCOA::where('kode_akun', '1111')->value('id');
        $rows = [];
        $urutan = 1;

        foreach ($npb->items as $it) {
            $anggaran = match ($periodeRealisasi) {
                'PROYEKSI' => (float) $it->nominal_proyeksi,
                'GABUNGAN' => round((float) $it->nominal_utama + (float) $it->nominal_proyeksi, 2),
                default    => (float) $it->nominal_utama,
            };

            if ($anggaran <= 0 && $periodeRealisasi !== 'GABUNGAN') {
                continue;
            }

            $rows[] = [
                'npb_item_id'        => $it->id,
                'urutan'             => $urutan++,
                'kategori'           => $it->kategori,
                'uraian'             => $it->uraian,
                'karakter'           => $it->karakter,
                'nominal_anggaran'   => $anggaran,
                'nominal_realisasi'  => $anggaran,
                'selisih'            => 0.0,
                'tanggal_realisasi'  => now()->toDateString(),
                'metode_pembayaran'  => 'BANK_TTC',
                'coa_beban_id'       => $it->coa_id,
                'coa_sumber_id'      => $defaultBankId,
                'no_referensi_bukti' => null,
                'lampiran_bukti'     => null,
                'status_item'        => 'TEREALISASI',
                'keterangan'         => null,
            ];
        }

        return $rows;
    }

    /**
     * Hitung ulang total anggaran, realisasi, selisih, dan sisa dana Penyelesaian NPB.
     */
    public function recalculateSettlement(NpbSettlement $settlement): NpbSettlement
    {
        $settlement->load(['items', 'npbRequest']);

        $totalAnggaran = 0.0;
        $totalRealisasi = 0.0;

        foreach ($settlement->items as $item) {
            $ang = round((float) $item->nominal_anggaran, 2);
            $real = round((float) $item->nominal_realisasi, 2);
            $sel = round($ang - $real, 2);

            if (abs((float) $item->selisih - $sel) >= 0.01) {
                $item->updateQuietly(['selisih' => $sel]);
            }

            $totalAnggaran += $ang;
            $totalRealisasi += $real;
        }

        $totalAnggaran = round($totalAnggaran, 2);
        $totalRealisasi = round($totalRealisasi, 2);
        $selisihAnggaran = round($totalAnggaran - $totalRealisasi, 2);
        $danaDicairkan = round((float) $settlement->total_dana_dicairkan, 2);
        $sisaDana = round($danaDicairkan - $totalRealisasi, 2);

        $tindak = $settlement->tindak_lanjut_sisa;
        if (empty($tindak)) {
            $tindak = abs($sisaDana) < 1.0 ? 'NIHIL' : ($sisaDana > 0 ? 'CARRY_OVER' : 'REIMBURSE_KURANG');
        }

        $settlement->updateQuietly([
            'total_anggaran'     => $totalAnggaran,
            'total_realisasi'    => $totalRealisasi,
            'selisih_anggaran'   => $selisihAnggaran,
            'sisa_dana'          => $sisaDana,
            'tindak_lanjut_sisa' => $tindak,
        ]);

        // Sinkron status NPB induk jika LPJ sudah di-approve
        if ($settlement->status === 'APPROVED' && $settlement->npbRequest) {
            $npb = $settlement->npbRequest;
            $hasPending = $settlement->items->contains(fn ($it) => in_array($it->status_item, ['SEBAGIAN', 'DITUNDA'], true));
            $nextStatus = $hasPending ? 'PARTIAL_SETTLED' : 'SETTLED';
            if ($npb->status !== 'SETTLED' || $nextStatus === 'SETTLED') {
                $npb->updateQuietly(['status' => $nextStatus]);
            }
        }

        return $settlement->refresh();
    }

    /**
     * Hapus jurnal pencairan NPB (NPB_DISBURSEMENT) dari Buku Besar.
     */
    public function deleteDisbursementFromLedger(NpbRequest $npb): void
    {
        DB::transaction(function () use ($npb) {
            CoaMovement::where('reference_type', 'NPB_DISBURSEMENT')
                ->where('reference_id', $npb->id)
                ->delete();

            if ($npb->exists) {
                $npb->updateQuietly(['is_pencairan_posted' => false]);
            }
        });
    }

    /**
     * Hapus NPB beserta seluruh jurnal Buku Besar terkait (NPB_DISBURSEMENT & LPJ) secara aman.
     */
    public function deleteRequestWithLedgerCleanup(NpbRequest $npb): void
    {
        DB::transaction(function () use ($npb) {
            CoaMovement::where('reference_type', 'NPB_DISBURSEMENT')
                ->where('reference_id', $npb->id)
                ->delete();

            foreach ($npb->settlements as $settlement) {
                CoaMovement::where('reference_type', 'NPB_SETTLEMENT')
                    ->where('reference_id', $settlement->id)
                    ->delete();

                $memorials = JournalEntry::where('no_referensi', $settlement->nomor_penyelesaian)->get();
                foreach ($memorials as $jm) {
                    CoaMovement::where('reference_type', 'JOURNAL_MEMORIAL')
                        ->where('reference_id', $jm->id)
                        ->delete();
                    $jm->lines()->delete();
                    $jm->delete();
                }
            }

            $npb->delete();
        });
    }

    /**
     * Posting Pencairan Dana NPB ke Buku Besar sebesar tepat nominal_dicairkan (mis. ACC 80 jt dari pengajuan 100 jt):
     * - DEBIT  : Rekening Bank/Kas Tujuan (111x)
     * - KREDIT : 3100 Modal Awal Pemilik / 2142 Hutang kepada Pemilik / 2144 Pinjaman PT Indigama
     */
    public function postDisbursementToLedger(NpbRequest $npb): bool
    {
        $nominal = round((float) $npb->nominal_dicairkan, 2);
        if ($nominal <= 0 || ! $npb->coa_tujuan_pencairan_id || $npb->sumber_dana_pencairan === 'DANA_INTERNAL') {
            $this->deleteDisbursementFromLedger($npb);
            return false;
        }

        $sumberCode = match ($npb->sumber_dana_pencairan) {
            'PINJAMAN_INDIGAMA' => '2144',
            'HUTANG_PEMILIK'    => '2142',
            default             => '3100',
        };
        $sumberCoa = MasterCOA::where('kode_akun', $sumberCode)->first();
        if (! $sumberCoa || (int) $sumberCoa->id === (int) $npb->coa_tujuan_pencairan_id) {
            return false;
        }

        $treasury = app(TreasuryService::class);
        $tanggal = $npb->tanggal_pencairan ? Carbon::parse($npb->tanggal_pencairan)->toDateString() : now()->toDateString();
        $refNo = $npb->nomor_pengajuan;
        $remarks = "Pencairan Dana {$refNo} — {$npb->perihal}";

        DB::transaction(function () use ($treasury, $npb, $sumberCoa, $tanggal, $refNo, $nominal, $remarks) {
            CoaMovement::where('reference_type', 'NPB_DISBURSEMENT')
                ->where('reference_id', $npb->id)
                ->delete();

            $treasury->postMovement([
                'coa_id'         => (int) $npb->coa_tujuan_pencairan_id,
                'tanggal'        => $tanggal,
                'reference_type' => 'NPB_DISBURSEMENT',
                'reference_id'   => $npb->id,
                'reference_no'   => $refNo,
                'movement_type'  => 'DEBIT',
                'nominal'        => $nominal,
                'remarks'        => $remarks,
                'created_by'     => auth()->id() ?? $npb->created_by,
            ]);

            $treasury->postMovement([
                'coa_id'         => (int) $sumberCoa->id,
                'tanggal'        => $tanggal,
                'reference_type' => 'NPB_DISBURSEMENT',
                'reference_id'   => $npb->id,
                'reference_no'   => $refNo,
                'movement_type'  => 'KREDIT',
                'nominal'        => $nominal,
                'remarks'        => "Sumber Pencairan {$refNo} ({$sumberCoa->nama_akun})",
                'created_by'     => auth()->id() ?? $npb->created_by,
            ]);

            $npb->updateQuietly(['is_pencairan_posted' => true]);
        });

        return true;
    }

    /**
     * Posting realisasi Penyelesaian NPB (LPJ) ke Buku Besar bila mode AUTO_JOURNAL dipilih.
     * - Item BANK_TTC / KAS_TTC: Debit COA Beban/Aset, Kredit COA Bank/Kas (reference_type = NPB_SETTLEMENT)
     * - Item TALANGAN_INDIGAMA: Membentuk Jurnal Memorial Non-Kas (Debit COA Beban/Aset, Kredit 2144 Pinjaman PT Indigama)
     */
    public function postSettlementToLedger(NpbSettlement $settlement): int
    {
        $settlement->load(['items', 'npbRequest']);
        if ($settlement->posting_mode !== 'AUTO_JOURNAL') {
            return 0;
        }

        $treasury = app(TreasuryService::class);
        $coaIndigamaId = (int) (MasterCOA::where('kode_akun', '2144')->value('id') ?: 0);
        $defaultBebanId = (int) (MasterCOA::where('kode_akun', '6610')->value('id') ?: 0);
        $defaultBankId = (int) (MasterCOA::where('kode_akun', '1111')->value('id') ?: 0);
        $defaultKasId = (int) (MasterCOA::where('kode_akun', '1116')->value('id') ?: 0);

        $postedCount = 0;

        DB::transaction(function () use (
            $settlement,
            $treasury,
            $coaIndigamaId,
            $defaultBebanId,
            $defaultBankId,
            $defaultKasId,
            &$postedCount
        ) {
            // Bersihkan postingan lama milik LPJ ini agar idempotent
            CoaMovement::where('reference_type', 'NPB_SETTLEMENT')
                ->where('reference_id', $settlement->id)
                ->delete();

            $oldMemorials = JournalEntry::where('no_referensi', $settlement->nomor_penyelesaian)->get();
            foreach ($oldMemorials as $jm) {
                CoaMovement::where('reference_type', 'JOURNAL_MEMORIAL')
                    ->where('reference_id', $jm->id)
                    ->delete();
                $jm->lines()->delete();
                $jm->delete();
            }

            $indigamaItems = [];
            $userId = auth()->id() ?? $settlement->created_by;

            foreach ($settlement->items as $item) {
                $real = round((float) $item->nominal_realisasi, 2);
                if ($real <= 0 || $item->metode_pembayaran === 'BELUM_DIBAYAR' || in_array($item->status_item, ['DITUNDA', 'DIBATALKAN'], true)) {
                    continue;
                }

                $debitCoaId = (int) ($item->coa_beban_id ?: $defaultBebanId);
                if (! $debitCoaId) {
                    continue;
                }

                if ($item->metode_pembayaran === 'TALANGAN_INDIGAMA') {
                    if ($coaIndigamaId) {
                        $indigamaItems[] = ['item' => $item, 'debit_coa' => $debitCoaId, 'nominal' => $real];
                    }
                    continue;
                }

                $creditCoaId = (int) ($item->coa_sumber_id ?: ($item->metode_pembayaran === 'KAS_TTC' ? $defaultKasId : $defaultBankId));
                if (! $creditCoaId || $creditCoaId === $debitCoaId) {
                    continue;
                }

                $tgl = $item->tanggal_realisasi
                    ? Carbon::parse($item->tanggal_realisasi)->toDateString()
                    : ($settlement->tanggal_penyelesaian ? Carbon::parse($settlement->tanggal_penyelesaian)->toDateString() : now()->toDateString());
                $refNo = $item->no_referensi_bukti ?: $settlement->nomor_penyelesaian;
                $ket = "[LPJ {$settlement->nomor_penyelesaian}] {$item->uraian}" . ($item->keterangan ? " — {$item->keterangan}" : '');

                $treasury->postMovement([
                    'coa_id'         => $debitCoaId,
                    'tanggal'        => $tgl,
                    'reference_type' => 'NPB_SETTLEMENT',
                    'reference_id'   => $settlement->id,
                    'reference_no'   => $refNo,
                    'movement_type'  => 'DEBIT',
                    'nominal'        => $real,
                    'remarks'        => $ket,
                    'created_by'     => $userId,
                ]);

                $treasury->postMovement([
                    'coa_id'         => $creditCoaId,
                    'tanggal'        => $tgl,
                    'reference_type' => 'NPB_SETTLEMENT',
                    'reference_id'   => $settlement->id,
                    'reference_no'   => $refNo,
                    'movement_type'  => 'KREDIT',
                    'nominal'        => $real,
                    'remarks'        => "Pembayaran {$ket}",
                    'created_by'     => $userId,
                ]);

                $postedCount++;
            }

            // Talangan PT Indigama -> 1 Jurnal Memorial Non-Kas langsung ke 2144 Pinjaman ke PT Indigama
            if (! empty($indigamaItems)) {
                $totalIndg = round(collect($indigamaItems)->sum('nominal'), 2);
                $tgl = $settlement->tanggal_penyelesaian
                    ? Carbon::parse($settlement->tanggal_penyelesaian)->toDateString()
                    : now()->toDateString();

                $je = JournalEntry::create([
                    'kode_jurnal'  => 'JRN-NPB-' . str_pad((string) $settlement->id, 4, '0', STR_PAD_LEFT),
                    'tanggal'      => $tgl,
                    'periode'      => Carbon::parse($tgl)->format('F Y'),
                    'gudang'       => 'TTC',
                    'jenis_jurnal' => 'MEMORIAL',
                    'no_referensi' => $settlement->nomor_penyelesaian,
                    'keterangan'   => "Realisasi Talangan PT Indigama (Non-Kas) — {$settlement->nomor_penyelesaian}",
                    'total_debit'  => $totalIndg,
                    'total_kredit' => $totalIndg,
                    'status'       => 'POSTED',
                    'created_by'   => $userId,
                ]);

                foreach ($indigamaItems as $entry) {
                    $it = $entry['item'];
                    JournalEntryLine::create([
                        'journal_entry_id' => $je->id,
                        'coa_id'           => $entry['debit_coa'],
                        'keterangan'       => "[Talangan Indigama | {$settlement->nomor_penyelesaian}] {$it->uraian}",
                        'debit'            => $entry['nominal'],
                        'kredit'           => 0,
                    ]);
                    JournalEntryLine::create([
                        'journal_entry_id' => $je->id,
                        'coa_id'           => $coaIndigamaId,
                        'keterangan'       => "Pinjaman ke PT Indigama — {$it->uraian} ({$settlement->nomor_penyelesaian})",
                        'debit'            => 0,
                        'kredit'           => $entry['nominal'],
                    ]);
                    $postedCount++;
                }

                $treasury->syncJournalMemorialMovements($je);
            }

            $settlement->updateQuietly(['is_posted_to_ledger' => $postedCount > 0]);
        });

        return $postedCount;
    }

    /**
     * Duplikasi pos Bulanan dari NPB sebelumnya untuk bulan berikutnya.
     */
    public function duplicateForNextMonth(NpbRequest $source): NpbRequest
    {
        $source->loadMissing(['items', 'fundPositions']);
        $nextDate = ($source->tanggal_pengajuan ? Carbon::parse($source->tanggal_pengajuan) : now())->addMonth();
        $bulan1 = $nextDate->translatedFormat('F Y');
        $bulan2 = $nextDate->copy()->addMonth()->translatedFormat('F Y');

        return DB::transaction(function () use ($source, $nextDate, $bulan1, $bulan2) {
            $clone = NpbRequest::create([
                'nomor_pengajuan'   => $this->generateNomorNpb($nextDate, $source->departemen_kode ?: 'KEU'),
                'tanggal_pengajuan' => $nextDate->toDateString(),
                'departemen_kode'   => $source->departemen_kode ?: 'KEU',
                'perihal'           => "Permohonan Pendanaan PT. Teknografi Tri Cawanaska Periode {$bulan1}-{$bulan2}",
                'periode_utama'     => $bulan1,
                'periode_proyeksi'  => $bulan2,
                'catatan'           => $source->catatan ?: NpbRequest::DEFAULT_CATATAN,
                'status'            => 'DRAFT',
                'disusun_oleh'      => $source->disusun_oleh ?: 'Bagian Keuangan',
                'mengetahui_nama'   => $source->mengetahui_nama ?: '',
                'diajukan_kepada'   => $source->diajukan_kepada ?: 'Direktur',
                'disetujui_oleh'    => $source->disetujui_oleh ?: 'Owner',
            ]);

            foreach ($this->getDefaultFundPositionsFromLedger($nextDate->toDateString()) as $fp) {
                $clone->fundPositions()->create($fp);
            }

            $urutan = 1;
            foreach ($source->items->where('karakter', 'Bulanan') as $it) {
                $nomUtama = (float) ($it->nominal_proyeksi > 0 ? $it->nominal_proyeksi : $it->nominal_utama);
                $clone->items()->create([
                    'urutan'           => $urutan++,
                    'kategori'         => $it->kategori,
                    'uraian'           => $it->uraian,
                    'karakter'         => $it->karakter,
                    'coa_id'           => $it->coa_id,
                    'nominal_utama'    => $nomUtama,
                    'nominal_proyeksi' => $nomUtama,
                    'keterangan'       => $it->keterangan,
                ]);
            }

            return $this->recalculateRequest($clone);
        });
    }
}
