<?php

namespace App\Services\Finance;

use App\Models\CoaMovement;
use App\Models\Gaji;
use App\Models\HrPayrollSetting;
use App\Models\MasterCOA;
use App\Models\NpbRequest;
use App\Models\NpbSettlement;
use App\Models\PayrollBorongan;
use App\Models\PembPinjaman;
use App\Models\PengPinjaman;
use App\Services\HR\PayrollService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinanceHrdSyncService
{
    public const INDONESIAN_MONTHS = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function __construct(
        protected TreasuryService $treasuryService,
        protected PayrollService $payrollService,
        protected NpbService $npbService
    ) {}

    /**
     * Ubah periode YYYY-MM menjadi label bulan Indonesia, misal "2026-11" -> "November 2026".
     */
    public function formatPeriodeLabel(string $periode): string
    {
        if (preg_match('/^(\d{4})-(\d{2})$/', trim($periode), $m)) {
            $monthName = self::INDONESIAN_MONTHS[(int) $m[2]] ?? $m[2];
            return "{$monthName} {$m[1]}";
        }

        return $periode;
    }

    /**
     * Parse label bulan Indonesia ("November 2026") menjadi periode YYYY-MM ("2026-11").
     */
    public function parseLabelToPeriode(?string $label): ?string
    {
        if (! $label) {
            return null;
        }
        if (preg_match('/(\d{4})-(\d{2})/', $label, $m)) {
            return "{$m[1]}-{$m[2]}";
        }
        $map = [
            'januari' => 1, 'jan' => 1, 'january' => 1,
            'februari' => 2, 'feb' => 2, 'february' => 2,
            'maret' => 3, 'mar' => 3, 'march' => 3,
            'april' => 4, 'apr' => 4,
            'mei' => 5, 'may' => 5,
            'juni' => 6, 'jun' => 6, 'june' => 6,
            'juli' => 7, 'jul' => 7, 'july' => 7,
            'agustus' => 8, 'agu' => 8, 'agt' => 8, 'aug' => 8, 'august' => 8,
            'september' => 9, 'sep' => 9,
            'oktober' => 10, 'okt' => 10, 'oct' => 10, 'october' => 10,
            'november' => 11, 'nov' => 11,
            'desember' => 12, 'des' => 12, 'dec' => 12, 'december' => 12,
        ];
        if (preg_match('/([A-Za-z]+)\s+(\d{4})/', $label, $m)) {
            $num = $map[strtolower($m[1])] ?? null;
            if ($num) {
                return sprintf('%04d-%02d', (int) $m[2], $num);
            }
        }

        return null;
    }

    /**
     * Laporan & Rekonsiliasi Real-Time 2-Arah Finance ↔ HRD untuk 1 periode (YYYY-MM).
     */
    public function getPeriodSummary(string $periode): array
    {
        $periode = trim($periode) ?: now()->format('Y-m');
        $periodeLabel = $this->formatPeriodeLabel($periode);

        // 1. Data Payroll Reguler / Harian / Freelance
        $slips = Gaji::with(['karyawan.departemen', 'accountKas', 'coaBeban', 'npbRequest'])
            ->where('periode', $periode)
            ->orderBy('id')
            ->get();

        $slipIds = $slips->pluck('id')->all();
        $postedSlipIds = empty($slipIds)
            ? collect()
            : CoaMovement::where('reference_type', 'PAYROLL')
                ->whereIn('reference_id', $slipIds)
                ->distinct()
                ->pluck('reference_id')
                ->flip();

        $glPayrollDebit = empty($slipIds)
            ? 0.0
            : round((float) CoaMovement::where('reference_type', 'PAYROLL')->whereIn('reference_id', $slipIds)->sum('debit'), 2);

        $glPayrollCash = empty($slipIds)
            ? 0.0
            : round((float) CoaMovement::where('reference_type', 'PAYROLL')
                ->whereIn('reference_id', $slipIds)
                ->whereHas('coa', fn ($q) => $q->where('kode_akun', 'like', '111%'))
                ->sum('kredit'), 2);

        // 2. Data Payroll Borongan / Part-Time
        $borongans = PayrollBorongan::with(['accountKas', 'coaBeban', 'npbRequest'])
            ->withCount('items')
            ->where('periode', $periode)
            ->orderBy('id')
            ->get();

        $boronganIds = $borongans->pluck('id')->all();
        $postedBoronganIds = empty($boronganIds)
            ? collect()
            : CoaMovement::where('reference_type', 'PAYROLL_BORONGAN')
                ->whereIn('reference_id', $boronganIds)
                ->distinct()
                ->pluck('reference_id')
                ->flip();

        $glBoronganDebit = empty($boronganIds)
            ? 0.0
            : round((float) CoaMovement::where('reference_type', 'PAYROLL_BORONGAN')->whereIn('reference_id', $boronganIds)->sum('debit'), 2);

        // 3. Alokasi Cost Center (HPP 5201 vs Sales 6101 vs Admin 6201 vs Umum 6301 + BPJS PT 6502)
        $costCenters = [];
        $bpjsPerusahaanTotal = 0.0;
        $bpjsKaryawanTotal = 0.0;
        $totalBebanGajiNet = 0.0;

        $setting = HrPayrollSetting::current();
        $pctPt = (float) ($setting->persen_bpjs_tk_perusahaan ?: 3.70);

        foreach ($slips as $slip) {
            $coa = $this->treasuryService->resolveBebanGajiCoa($slip);
            $coaKey = $coa ? ($coa->kode_akun . ' - ' . $coa->nama_akun) : '6301 - Gaji Karyawan Umum';
            $coaCode = $coa?->kode_akun ?? '6301';

            $thp = round((float) $slip->gaji_bersih, 2);
            $potPinj = round((float) $slip->potongan_pinjaman, 2);
            $potBpjs = round((float) $slip->potongan_bpjs, 2);
            $potPph = round((float) $slip->potongan_pph21, 2);
            $potLain = round((float) $slip->potongan_lain, 2);
            $bebanGaji = round($thp + $potPinj + $potBpjs + $potPph + $potLain, 2);

            // Jika bpjs_perusahaan tersimpan gunakan nilainya; jika belum & ada potongan BPJS karyawan 2%, hitung estimasi 3.7% dari gaji pokok
            $bpjsPt = round((float) ($slip->bpjs_perusahaan ?? 0), 2);
            if ($bpjsPt <= 0 && $potBpjs > 0 && (float) $slip->gaji_pokok > 0) {
                $bpjsPt = round((float) $slip->gaji_pokok * ($pctPt / 100), 2);
            }

            $bpjsKaryawanTotal += $potBpjs;
            $bpjsPerusahaanTotal += $bpjsPt;
            $totalBebanGajiNet += $bebanGaji;

            if (! isset($costCenters[$coaKey])) {
                $costCenters[$coaKey] = [
                    'kode_akun'   => $coaCode,
                    'nama_akun'   => $coa?->nama_akun ?? 'Gaji Karyawan Umum',
                    'kelompok'    => str_starts_with($coaCode, '5') ? 'HPP / COGS (Produksi)' : 'Beban Operasional (OPEX)',
                    'headcount'   => 0,
                    'beban_gaji'  => 0.0,
                    'thp'         => 0.0,
                ];
            }
            $costCenters[$coaKey]['headcount']++;
            $costCenters[$coaKey]['beban_gaji'] = round($costCenters[$coaKey]['beban_gaji'] + $bebanGaji, 2);
            $costCenters[$coaKey]['thp'] = round($costCenters[$coaKey]['thp'] + $thp, 2);
        }

        // Masukkan Payroll Borongan ke Cost Center Produksi (5201)
        if ($borongans->isNotEmpty()) {
            $borCoa = $this->treasuryService->resolveCoa(['5201', '5200', '6301'], ['Gaji Produksi']);
            $borKey = $borCoa ? ($borCoa->kode_akun . ' - ' . $borCoa->nama_akun) : '5201 - Gaji Produksi';
            $borTotal = round((float) $borongans->sum('grand_total'), 2);
            $borWorkers = (int) $borongans->sum('items_count');

            if (! isset($costCenters[$borKey])) {
                $costCenters[$borKey] = [
                    'kode_akun'   => $borCoa?->kode_akun ?? '5201',
                    'nama_akun'   => $borCoa?->nama_akun ?? 'Gaji Produksi',
                    'kelompok'    => 'HPP / COGS (Produksi & Borongan)',
                    'headcount'   => 0,
                    'beban_gaji'  => 0.0,
                    'thp'         => 0.0,
                ];
            }
            $costCenters[$borKey]['headcount'] += $borWorkers;
            $costCenters[$borKey]['beban_gaji'] = round($costCenters[$borKey]['beban_gaji'] + $borTotal, 2);
            $costCenters[$borKey]['thp'] = round($costCenters[$borKey]['thp'] + $borTotal, 2);
        }

        $bpjsKaryawanTotal = round($bpjsKaryawanTotal, 2);
        $bpjsPerusahaanTotal = round($bpjsPerusahaanTotal, 2);
        $totalBpjsSetor = round($bpjsKaryawanTotal + $bpjsPerusahaanTotal, 2);
        $totalBorongan = round((float) $borongans->sum('grand_total'), 2);
        $totalBebanTenagaKerja = round($totalBebanGajiNet + $bpjsPerusahaanTotal + $totalBorongan, 2);

        // 4. Status Penyetoran BPJS periode ini di GL
        $bpjsRefId = (int) preg_replace('/\D/', '', $periode) ?: 0;
        $glBpjsRemitted = $bpjsRefId > 0
            ? round((float) CoaMovement::where('reference_type', 'BPJS_REMITTANCE')->where('reference_id', $bpjsRefId)->sum('debit'), 2)
            : 0.0;

        $hutangBpjsCoa = $this->treasuryService->resolveHutangBpjsCoa();
        $glSaldoHutangBpjs = $hutangBpjsCoa
            ? round(-1 * $this->treasuryService->getCurrentBalance($hutangBpjsCoa->id), 2)
            : 0.0;

        // 5. Rekonsiliasi Piutang Karyawan / Kasbon (HRD vs COA 1131 Finance)
        $activeLoans = PengPinjaman::with(['karyawan', 'pembayaran', 'accountKas'])
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Rejected');
            })
            ->orderByDesc('id')
            ->get()
            ->map(function (PengPinjaman $p) {
                $terbayar = round((float) $p->pembayaran->sum('nominal_pembayaran'), 2);
                $sisa = max(0.0, round((float) $p->nilai_pinjaman - $terbayar, 2));
                $isGlPosted = CoaMovement::where('reference_type', 'PINJAMAN_CAIR')
                    ->where('reference_id', $p->id)
                    ->exists();
                return [
                    'id'              => $p->id,
                    'nomor_bukti'     => $p->nomor_bukti,
                    'karyawan'        => $p->karyawan?->nama ?? '-',
                    'nip'             => $p->karyawan?->nip ?? '-',
                    'nilai_pinjaman'  => round((float) $p->nilai_pinjaman, 2),
                    'cicilan'         => round((float) $p->cicilan_per_bulan, 2),
                    'terbayar'        => $terbayar,
                    'sisa'            => $sisa,
                    'status'          => $p->status,
                    'is_disbursed'    => (bool) $p->is_disbursed,
                    'is_gl_posted'    => $isGlPosted,
                ];
            })
            ->filter(fn ($l) => $l['sisa'] > 0.01 || ! $l['is_disbursed'])
            ->values();

        $totalSisaPinjamanHrd = round((float) $activeLoans->sum('sisa'), 2);
        $piutangKaryawanCoa = $this->treasuryService->resolvePiutangKaryawanCoa();
        $glSaldoPiutangKaryawan = $piutangKaryawanCoa
            ? round($this->treasuryService->getCurrentBalance($piutangKaryawanCoa->id), 2)
            : 0.0;
        $kasbonVariance = round($totalSisaPinjamanHrd - $glSaldoPiutangKaryawan, 2);

        // 6. Rekonsiliasi NPB ↔ HRD untuk periode ini
        $matchedNpb = NpbRequest::with('items')
            ->where(function ($q) use ($periodeLabel, $periode) {
                $q->where('periode_utama', 'like', "%{$periodeLabel}%")
                    ->orWhere('periode_utama', 'like', "%{$periode}%");
            })
            ->orderByDesc('id')
            ->first();

        $npbBudgetGaji = 0.0;
        $npbBudgetBpjs = 0.0;
        $npbBudgetBorongan = 0.0;

        if ($matchedNpb) {
            foreach ($matchedNpb->items as $it) {
                $u = strtolower((string) $it->uraian);
                if (str_contains($u, 'borongan') || str_contains($u, 'freelance')) {
                    $npbBudgetBorongan += (float) $it->nominal_utama;
                } elseif (str_contains($u, 'bpjs')) {
                    $npbBudgetBpjs += (float) $it->nominal_utama;
                } elseif (str_contains($u, 'gaji') || str_contains($u, 'payroll')) {
                    $npbBudgetGaji += (float) $it->nominal_utama;
                }
            }
        }

        $totalGajiPokok = round((float) $slips->sum('gaji_pokok'), 2);
        $totalPendapatan = round((float) $slips->sum('total_pendapatan'), 2);
        $totalPotAbsen = round((float) $slips->sum('potongan_kehadiran') + (float) $slips->sum('potongan_tunjangan'), 2);
        $totalPotKasbon = round((float) $slips->sum('potongan_pinjaman'), 2);
        $totalPph21 = round((float) $slips->sum('potongan_pph21'), 2);
        $totalThp = round((float) $slips->sum('gaji_bersih'), 2);
        $totalKasKeluar = round($totalThp + $totalBorongan, 2);

        $paidCount = $slips->where('status', 'Paid')->count();
        $approvedCount = $slips->where('status', 'Approved')->count();
        $draftCount = $slips->where('status', 'Draft')->count();
        $postedCount = $postedSlipIds->count();
        $unpostedPaidCount = $slips->where('status', 'Paid')->filter(fn ($s) => ! $postedSlipIds->has($s->id))->count();

        $slipRows = $slips->map(function (Gaji $s) use ($postedSlipIds, $pctPt) {
            $coa = $this->treasuryService->resolveBebanGajiCoa($s);
            $bpjsPt = (float) ($s->bpjs_perusahaan ?? 0);
            if ($bpjsPt <= 0 && (float) $s->potongan_bpjs > 0 && (float) $s->gaji_pokok > 0) {
                $bpjsPt = round((float) $s->gaji_pokok * ($pctPt / 100), 2);
            }
            return [
                'id'               => $s->id,
                'kode_slip'        => $s->kode_slip,
                'nip'              => $s->karyawan?->nip ?: '-',
                'nama'             => $s->karyawan?->nama ?: '-',
                'departemen'       => $s->karyawan?->departemen?->nama_departemen ?: '-',
                'jabatan'          => $s->karyawan?->jabatan ?: '-',
                'coa_label'        => $coa ? "{$coa->kode_akun} · {$coa->nama_akun}" : '-',
                'gaji_pokok'       => round((float) $s->gaji_pokok, 2),
                'potongan_kasbon'  => round((float) $s->potongan_pinjaman, 2),
                'potongan_bpjs'    => round((float) $s->potongan_bpjs, 2),
                'bpjs_perusahaan'  => round($bpjsPt, 2),
                'gaji_bersih'      => round((float) $s->gaji_bersih, 2),
                'status'           => $s->status,
                'is_gl_posted'     => $postedSlipIds->has($s->id),
                'account_kas'      => $s->accountKas ? "{$s->accountKas->kode_akun} - {$s->accountKas->nama_akun}" : '-',
            ];
        })->all();

        $boronganRows = $borongans->map(fn (PayrollBorongan $pb) => [
            'id'           => $pb->id,
            'kode_bukti'   => $pb->kode_bukti,
            'rentang'      => ($pb->tanggal_mulai?->format('d/m') ?? '-') . ' - ' . ($pb->tanggal_selesai?->format('d/m/Y') ?? '-'),
            'pekerja'      => (int) $pb->items_count,
            'grand_total'  => round((float) $pb->grand_total, 2),
            'status'       => $pb->status,
            'is_gl_posted' => $postedBoronganIds->has($pb->id),
        ])->all();

        return [
            'periode'                    => $periode,
            'periode_label'              => $periodeLabel,
            'slip_count'                 => $slips->count(),
            'draft_count'                => $draftCount,
            'approved_count'             => $approvedCount,
            'paid_count'                 => $paidCount,
            'posted_count'               => $postedCount,
            'unposted_paid_count'        => $unpostedPaidCount,
            'borongan_count'             => $borongans->count(),
            'total_gaji_pokok'           => $totalGajiPokok,
            'total_pendapatan'           => $totalPendapatan,
            'total_beban_gaji_net'       => round($totalBebanGajiNet, 2),
            'total_potongan_absen'       => $totalPotAbsen,
            'total_potongan_kasbon'      => $totalPotKasbon,
            'total_bpjs_karyawan'        => $bpjsKaryawanTotal,
            'total_bpjs_perusahaan'      => $bpjsPerusahaanTotal,
            'total_bpjs_setor'           => $totalBpjsSetor,
            'total_pph21'                => $totalPph21,
            'total_thp'                  => $totalThp,
            'total_borongan'             => $totalBorongan,
            'total_kas_keluar'           => $totalKasKeluar,
            'total_beban_tenaga_kerja'   => $totalBebanTenagaKerja,
            'gl_payroll_debit'           => $glPayrollDebit,
            'gl_payroll_cash'            => $glPayrollCash,
            'gl_borongan_debit'          => $glBoronganDebit,
            'gl_total_posted'            => round($glPayrollDebit + $glBoronganDebit, 2),
            'gl_bpjs_remitted'           => $glBpjsRemitted,
            'gl_saldo_hutang_bpjs'       => $glSaldoHutangBpjs,
            'cost_centers'               => array_values($costCenters),
            'active_loans'               => $activeLoans->all(),
            'total_sisa_pinjaman_hrd'    => $totalSisaPinjamanHrd,
            'gl_saldo_piutang_karyawan'  => $glSaldoPiutangKaryawan,
            'kasbon_variance'            => $kasbonVariance,
            'npb'                        => $matchedNpb ? [
                'id'               => $matchedNpb->id,
                'nomor_pengajuan'  => $matchedNpb->nomor_pengajuan,
                'status'           => $matchedNpb->status,
                'budget_gaji'      => round($npbBudgetGaji, 2),
                'budget_bpjs'      => round($npbBudgetBpjs, 2),
                'budget_borongan'  => round($npbBudgetBorongan, 2),
                'variance_gaji'    => round($npbBudgetGaji - $totalThp, 2),
                'variance_bpjs'    => round($npbBudgetBpjs - $totalBpjsSetor, 2),
                'variance_borongan'=> round($npbBudgetBorongan - $totalBorongan, 2),
            ] : null,
            'slip_rows'                  => $slipRows,
            'borongan_rows'              => $boronganRows,
        ];
    }

    /**
     * Susun item NPB siap pakai dari data aktual Payroll & Borongan HRD periode tertentu.
     */
    public function buildNpbPayrollItems(string $periode, string $basisGaji = 'THP'): array
    {
        $summary = $this->getPeriodSummary($periode);
        $coaGaji = $this->treasuryService->resolveBebanGajiCoa(null);
        $coaBpjs = $this->treasuryService->resolveBebanBpjsCoa();
        $coaProd = $this->treasuryService->resolveCoa(['5201', '5200', '6301'], ['Gaji Produksi']);

        $items = [];

        // 1. Gaji Karyawan
        $nomGaji = match ($basisGaji) {
            'GAJI_POKOK' => $summary['total_gaji_pokok'],
            'GROSS'      => $summary['total_beban_gaji_net'],
            default      => $summary['total_thp'],
        };

        if ($nomGaji > 0) {
            $ketGaji = sprintf(
                'Auto HRD %s (%d karyawan · Basis %s · GP Rp %s · Pot.Kasbon Rp %s · THP Rp %s)',
                $periode,
                $summary['slip_count'],
                $basisGaji,
                number_format($summary['total_gaji_pokok'], 0, ',', '.'),
                number_format($summary['total_potongan_kasbon'], 0, ',', '.'),
                number_format($summary['total_thp'], 0, ',', '.')
            );
            $items[] = [
                'kategori'         => 'A_KEWAJIBAN_TETAP',
                'uraian'           => 'Gaji Karyawan',
                'karakter'         => 'Bulanan',
                'coa_id'           => $coaGaji?->id,
                'nominal_utama'    => $nomGaji,
                'nominal_proyeksi' => $nomGaji,
                'keterangan'       => $ketGaji,
                'sync_key'         => 'GAJI',
            ];
        }

        // 2. BPJS Ketenagakerjaan (Total Setor = Karyawan 2% + Perusahaan 3.7%)
        if ($summary['total_bpjs_setor'] > 0) {
            $ketBpjs = sprintf(
                'Auto HRD %s (Karyawan 2%% Rp %s + Perusahaan 3.7%% Rp %s)',
                $periode,
                number_format($summary['total_bpjs_karyawan'], 0, ',', '.'),
                number_format($summary['total_bpjs_perusahaan'], 0, ',', '.')
            );
            $items[] = [
                'kategori'         => 'A_KEWAJIBAN_TETAP',
                'uraian'           => 'BPJS Ketenagakerjaan',
                'karakter'         => 'Bulanan',
                'coa_id'           => $coaBpjs?->id,
                'nominal_utama'    => $summary['total_bpjs_setor'],
                'nominal_proyeksi' => $summary['total_bpjs_setor'],
                'keterangan'       => $ketBpjs,
                'sync_key'         => 'BPJS',
            ];
        }

        // 3. Payroll Borongan / Part-Time
        if ($summary['total_borongan'] > 0) {
            $ketBor = sprintf(
                'Auto HRD Borongan %s (%d batch borongan produksi)',
                $periode,
                $summary['borongan_count']
            );
            $items[] = [
                'kategori'         => 'C_PRODUKSI_FASILITAS',
                'uraian'           => 'Upah Borongan / Part-Time Produksi',
                'karakter'         => 'Bulanan',
                'coa_id'           => $coaProd?->id,
                'nominal_utama'    => $summary['total_borongan'],
                'nominal_proyeksi' => $summary['total_borongan'],
                'keterangan'       => $ketBor,
                'sync_key'         => 'BORONGAN',
            ];
        }

        return $items;
    }

    /**
     * Sinkronisasi 1-klik: Dorong angka aktual Gaji, BPJS & Borongan HRD ke Nota Pengajuan Biaya (NPB).
     */
    public function syncPeriodToNpb(string $periode, ?int $npbRequestId = null, string $basisGaji = 'THP'): array
    {
        $hrdItems = $this->buildNpbPayrollItems($periode, $basisGaji);
        if (empty($hrdItems)) {
            throw ValidationException::withMessages([
                'periode' => "Belum ada data payroll atau borongan di HRD untuk periode {$periode}.",
            ]);
        }

        return DB::transaction(function () use ($periode, $npbRequestId, $hrdItems) {
            $periodeLabel = $this->formatPeriodeLabel($periode);

            $npb = $npbRequestId
                ? NpbRequest::with('items')->findOrFail($npbRequestId)
                : NpbRequest::with('items')
                    ->where('periode_utama', 'like', "%{$periodeLabel}%")
                    ->orderByDesc('id')
                    ->first();

            $createdNewNpb = false;
            if (! $npb) {
                $dt = Carbon::createFromFormat('Y-m', $periode)->startOfMonth();
                $b1 = $this->formatPeriodeLabel($periode);
                $b2 = $this->formatPeriodeLabel($dt->copy()->addMonth()->format('Y-m'));

                $npb = NpbRequest::create([
                    'nomor_pengajuan'   => $this->npbService->generateNomorNpb($dt, 'KEU'),
                    'tanggal_pengajuan' => now()->toDateString(),
                    'departemen_kode'   => 'KEU',
                    'perihal'           => "Permohonan Pendanaan PT. Teknografi Tri Cawanaska Periode {$b1}-{$b2}",
                    'periode_utama'     => $b1,
                    'periode_proyeksi'  => $b2,
                    'catatan'           => NpbRequest::DEFAULT_CATATAN,
                    'status'            => 'DRAFT',
                    'disusun_oleh'      => 'Bagian Keuangan',
                    'mengetahui_nama'   => '',
                    'diajukan_kepada'   => 'Direktur',
                    'disetujui_oleh'    => 'Owner',
                    'created_by'        => auth()->id(),
                ]);

                foreach ($this->npbService->getDefaultFundPositionsFromLedger(now()->toDateString()) as $fp) {
                    $npb->fundPositions()->create($fp);
                }
                $createdNewNpb = true;
                $npb->load('items');
            }

            $updatedCount = 0;
            $addedCount = 0;
            $nextUrutan = ((int) $npb->items->max('urutan')) + 1;

            foreach ($hrdItems as $entry) {
                $syncKey = $entry['sync_key'];
                unset($entry['sync_key']);

                $existing = $npb->items->first(function ($it) use ($syncKey) {
                    $u = strtolower((string) $it->uraian);
                    return match ($syncKey) {
                        'GAJI'     => str_contains($u, 'gaji') && ! str_contains($u, 'borong'),
                        'BPJS'     => str_contains($u, 'bpjs'),
                        'BORONGAN' => str_contains($u, 'borong') || str_contains($u, 'freelance'),
                        default    => false,
                    };
                });

                if ($existing) {
                    $existing->update([
                        'coa_id'           => $entry['coa_id'] ?: $existing->coa_id,
                        'nominal_utama'    => $entry['nominal_utama'],
                        'nominal_proyeksi' => $entry['nominal_proyeksi'],
                        'keterangan'       => $entry['keterangan'],
                    ]);
                    $updatedCount++;
                } else {
                    $entry['urutan'] = $nextUrutan++;
                    $npb->items()->create($entry);
                    $addedCount++;
                }
            }

            // Kaitkan referensi NPB ke slip gaji & borongan periode tersebut
            Gaji::where('periode', $periode)->update(['npb_request_id' => $npb->id]);
            PayrollBorongan::where('periode', $periode)->update(['npb_request_id' => $npb->id]);

            $npb = $this->npbService->recalculateRequest($npb);

            return [
                'npb_id'          => $npb->id,
                'nomor_pengajuan' => $npb->nomor_pengajuan,
                'created_new_npb' => $createdNewNpb,
                'updated_items'   => $updatedCount,
                'added_items'     => $addedCount,
                'total_kebutuhan' => (float) $npb->total_kebutuhan_utama,
            ];
        });
    }

    /**
     * Bayar Massal & Posting Jurnal Multi-Line PSAK untuk seluruh slip periode tertentu.
     */
    public function batchApproveAndPayPeriod(
        string $periode,
        int $accountKasId,
        string $metode = 'Transfer Bank',
        bool $postToLedger = true,
        bool $includeBorongan = false
    ): array {
        return DB::transaction(function () use ($periode, $accountKasId, $metode, $postToLedger, $includeBorongan) {
            $slips = Gaji::where('periode', $periode)->orderBy('id')->get();
            $paidSlips = 0;
            $repostedSlips = 0;
            $totalCash = 0.0;

            foreach ($slips as $slip) {
                if ($slip->status !== 'Paid') {
                    $this->payrollService->payPayroll($slip, $accountKasId, $metode, $postToLedger);
                    $paidSlips++;
                } elseif ($postToLedger) {
                    $slip->update([
                        'account_kas_id' => $accountKasId,
                        'post_to_ledger' => true,
                    ]);
                    $this->treasuryService->syncPayroll($slip->refresh());
                    $repostedSlips++;
                }
                $totalCash += (float) $slip->gaji_bersih;
            }

            $paidBorongan = 0;
            if ($includeBorongan) {
                $borongans = PayrollBorongan::where('periode', $periode)->orderBy('id')->get();
                foreach ($borongans as $pb) {
                    if ($pb->status !== 'Paid') {
                        $this->payrollService->payBorongan($pb, $accountKasId, $metode, $postToLedger);
                        $paidBorongan++;
                    } elseif ($postToLedger) {
                        $pb->update([
                            'account_kas_id' => $accountKasId,
                            'post_to_ledger' => true,
                        ]);
                        $this->treasuryService->syncPayrollBorongan($pb->refresh());
                    }
                    $totalCash += (float) $pb->grand_total;
                }
            }

            return [
                'periode'        => $periode,
                'paid_slips'     => $paidSlips,
                'reposted_slips' => $repostedSlips,
                'paid_borongan'  => $paidBorongan,
                'total_cash'     => round($totalCash, 2),
                'post_to_ledger' => $postToLedger,
            ];
        });
    }

    /**
     * Pencairan Dana Pinjaman / Kasbon Karyawan dari Finance ke Karyawan (DEBIT 1131, KREDIT 111x).
     */
    public function disbursePinjaman(
        PengPinjaman $pinjaman,
        int $accountKasId,
        ?string $tanggal = null,
        bool $postToLedger = true
    ): PengPinjaman {
        return DB::transaction(function () use ($pinjaman, $accountKasId, $tanggal, $postToLedger) {
            $pinjaman = PengPinjaman::lockForUpdate()->findOrFail($pinjaman->id);
            if ($pinjaman->status === 'Rejected') {
                throw ValidationException::withMessages([
                    'status' => 'Pinjaman yang ditolak (Rejected) tidak dapat dicairkan.',
                ]);
            }

            $pinjaman->update([
                'status'            => 'Approved',
                'approved_by'       => $pinjaman->approved_by ?: (auth()->user()?->name ?? 'Finance'),
                'approved_at'       => $pinjaman->approved_at ?: now(),
                'account_kas_id'    => $accountKasId,
                'tanggal_pencairan' => $tanggal ?: now()->toDateString(),
                'is_disbursed'      => true,
                'post_to_ledger'    => $postToLedger,
            ]);

            $this->treasuryService->syncPinjamanDisbursement($pinjaman->refresh());

            return $pinjaman;
        });
    }

    /**
     * Daftar seluruh periode yang tersedia di HRD untuk dropdown Command Center.
     */
    public function getAvailablePeriods(): array
    {
        $gajiPeriods = Gaji::select('periode')->distinct()->pluck('periode')->all();
        $borPeriods = PayrollBorongan::select('periode')->distinct()->pluck('periode')->all();
        $all = array_unique(array_filter(array_merge($gajiPeriods, $borPeriods, [now()->format('Y-m')])));
        rsort($all);

        $opts = [];
        foreach ($all as $p) {
            $opts[$p] = "{$p} — " . $this->formatPeriodeLabel($p);
        }

        return $opts;
    }
}
