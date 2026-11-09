<?php

namespace App\Filament\Admin\Resources\CoaLedgerResource\Pages;

use App\Filament\Admin\Resources\CoaLedgerResource;
use App\Models\CoaMovement;
use App\Models\MasterCOA;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class ListCoaLedgers extends Page
{
    protected static string $resource = CoaLedgerResource::class;
    protected static ?string $title = 'General Ledger — Buku Besar & Jurnal Umum';
    protected static string $view = 'filament.admin.pages.general-ledger';

    public string $startDate = '';
    public string $endDate = '';
    public string $selectedCoaId = '';
    public string $selectedRefType = '';
    public string $searchQuery = '';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function mount(): void
    {
        $minDate = CoaMovement::min('tanggal');
        $maxDate = CoaMovement::max('tanggal');

        $this->startDate = $minDate
            ? Carbon::parse($minDate)->startOfYear()->toDateString()
            : now()->startOfYear()->toDateString();

        $this->endDate = $maxDate
            ? Carbon::parse($maxDate)->endOfMonth()->toDateString()
            : now()->endOfMonth()->toDateString();
    }

    public function setPreset(string $preset): void
    {
        $now = now();
        if ($preset === 'this_month') {
            $this->startDate = $now->copy()->startOfMonth()->toDateString();
            $this->endDate = $now->copy()->endOfMonth()->toDateString();
        } elseif ($preset === 'last_month') {
            $last = $now->copy()->subMonth();
            $this->startDate = $last->copy()->startOfMonth()->toDateString();
            $this->endDate = $last->copy()->endOfMonth()->toDateString();
        } elseif ($preset === 'this_year') {
            $this->startDate = '2026-01-01';
            $this->endDate = '2026-12-31';
        }
    }

    public function filterByAccount(int|string $coaId): void
    {
        $this->selectedCoaId = (string) $coaId === $this->selectedCoaId ? '' : (string) $coaId;
    }

    public function resetFilters(): void
    {
        $this->selectedCoaId = '';
        $this->selectedRefType = '';
        $this->searchQuery = '';
        $this->startDate = '2026-01-01';
        $this->endDate = '2026-12-31';
    }

    public function getLedgerDataProperty(): array
    {
        $start = $this->startDate ?: '2026-01-01';
        $end = $this->endDate ?: '2026-12-31';

        $allMovementsInPeriod = CoaMovement::with(['coa', 'user'])
            ->whereDate('tanggal', '>=', $start)
            ->whereDate('tanggal', '<=', $end)
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Hitung saldo berjalan per akun secara akurat
        $runningByCoa = [];
        foreach ($allMovementsInPeriod->groupBy('coa_id') as $coaId => $group) {
            $coa = $group->first()?->coa;
            $code = trim((string) ($coa?->kode_akun ?? ''));
            $isCreditNormal = str_starts_with($code, '2') || str_starts_with($code, '3') || str_starts_with($code, '4');

            $openDebit = (float) ($coa?->saldo_debit ?? 0);
            $openKredit = (float) ($coa?->saldo_kredit ?? 0);

            $prior = CoaMovement::where('coa_id', $coaId)
                ->whereDate('tanggal', '<', $start)
                ->selectRaw('COALESCE(SUM(debit),0) as d, COALESCE(SUM(kredit),0) as k')
                ->first();

            $running = $isCreditNormal
                ? (($openKredit - $openDebit) + ((float) ($prior->k ?? 0) - (float) ($prior->d ?? 0)))
                : (($openDebit - $openKredit) + ((float) ($prior->d ?? 0) - (float) ($prior->k ?? 0)));

            foreach ($group as $m) {
                $d = (float) $m->debit;
                $k = (float) $m->kredit;
                $running += $isCreditNormal ? ($k - $d) : ($d - $k);
                $runningByCoa[$m->id] = round($running, 2);
            }
        }

        // Ringkasan per Akun yang aktif pada periode ini
        $accountSummaries = [];
        foreach ($allMovementsInPeriod->groupBy('coa_id') as $coaId => $group) {
            $coa = $group->first()?->coa;
            if (! $coa) {
                continue;
            }
            $code = trim((string) $coa->kode_akun);
            $isCreditNormal = str_starts_with($code, '2') || str_starts_with($code, '3') || str_starts_with($code, '4');
            $sumD = round((float) $group->sum('debit'), 2);
            $sumK = round((float) $group->sum('kredit'), 2);
            $ending = $isCreditNormal ? round($sumK - $sumD, 2) : round($sumD - $sumK, 2);

            $accountSummaries[] = [
                'coa_id'     => $coa->id,
                'kode_akun'  => $code,
                'nama_akun'  => $coa->nama_akun,
                'posisi'     => $isCreditNormal ? 'KREDIT' : 'DEBIT',
                'count'      => $group->count(),
                'debit'      => $sumD,
                'kredit'     => $sumK,
                'saldo_akhir'=> $ending,
            ];
        }
        usort($accountSummaries, fn ($a, $b) => strcmp($a['kode_akun'], $b['kode_akun']));

        // Filter baris sesuai pilihan user
        $filtered = $allMovementsInPeriod->filter(function (CoaMovement $m) {
            if ($this->selectedCoaId !== '' && (int) $m->coa_id !== (int) $this->selectedCoaId) {
                return false;
            }
            if ($this->selectedRefType !== '' && (string) $m->reference_type !== $this->selectedRefType) {
                return false;
            }
            if (trim($this->searchQuery) !== '') {
                $q = mb_strtolower(trim($this->searchQuery));
                $haystack = mb_strtolower(
                    ($m->reference_no ?? '') . ' ' .
                    ($m->remarks ?? '') . ' ' .
                    ($m->coa?->kode_akun ?? '') . ' ' .
                    ($m->coa?->nama_akun ?? '')
                );
                if (! str_contains($haystack, $q)) {
                    return false;
                }
            }
            return true;
        })->values();

        $rows = [];
        foreach ($filtered as $m) {
            $code = trim((string) ($m->coa?->kode_akun ?? '-'));
            $refMeta = match ($m->reference_type) {
                'INVOICE'          => ['label' => 'Invoice Penjualan',      'bg' => '#dbeafe', 'fg' => '#1e40af'],
                'INV_PAYMENT'      => ['label' => 'Pembayaran Bon/Invoice', 'bg' => '#dcfce7', 'fg' => '#15803d'],
                'OPERASIONAL_PAY'  => ['label' => 'Operasional / Komisi',   'bg' => '#fef3c7', 'fg' => '#b45309'],
                'JOURNAL_MEMORIAL' => ['label' => 'Jurnal Memorial / HPP',  'bg' => '#f3e8ff', 'fg' => '#6b21a8'],
                'TRANS_KAS'        => ['label' => 'Kas Masuk/Keluar',       'bg' => '#e0f2fe', 'fg' => '#0369a1'],
                'TRANS_BANK'       => ['label' => 'Bank Masuk/Keluar',      'bg' => '#e0e7ff', 'fg' => '#3730a3'],
                'PO_BILLING'       => ['label' => 'Tagihan PO',             'bg' => '#fee2e2', 'fg' => '#b91c1c'],
                'PO_BILL_PAY'      => ['label' => 'Pembayaran PO',          'bg' => '#ffedd5', 'fg' => '#c2410c'],
                default            => ['label' => $m->reference_type ?: '-', 'bg' => '#f1f5f9', 'fg' => '#475569'],
            };

            $rows[] = [
                'id'              => $m->id,
                'tanggal'         => Carbon::parse($m->tanggal)->format('d/m/Y'),
                'coa_id'          => $m->coa_id,
                'kode_akun'       => $code,
                'nama_akun'       => $m->coa?->nama_akun ?? '-',
                'ref_type'        => $m->reference_type,
                'ref_label'       => $refMeta['label'],
                'ref_bg'          => $refMeta['bg'],
                'ref_fg'          => $refMeta['fg'],
                'reference_no'    => $m->reference_no ?: '-',
                'remarks'         => $m->remarks ?: '-',
                'debit'           => round((float) $m->debit, 2),
                'kredit'          => round((float) $m->kredit, 2),
                'running_balance' => $runningByCoa[$m->id] ?? 0.0,
            ];
        }

        $totalDebit = round(collect($rows)->sum('debit'), 2);
        $totalKredit = round(collect($rows)->sum('kredit'), 2);
        $selisih = round(abs($totalDebit - $totalKredit), 2);

        $coaOptions = MasterCOA::whereIn('id', $allMovementsInPeriod->pluck('coa_id')->unique())
            ->orderBy('kode_akun')
            ->get()
            ->mapWithKeys(fn ($c) => [$c->id => trim($c->kode_akun) . ' — ' . $c->nama_akun])
            ->toArray();

        return [
            'rows'              => $rows,
            'account_summaries' => $accountSummaries,
            'coa_options'       => $coaOptions,
            'total_debit'       => $totalDebit,
            'total_kredit'      => $totalKredit,
            'selisih'           => $selisih,
            'is_balanced'       => $selisih < 1.0,
            'debit_count'       => collect($rows)->where('debit', '>', 0)->count(),
            'kredit_count'      => collect($rows)->where('kredit', '>', 0)->count(),
            'total_rows'        => count($rows),
            'active_coa_count'  => count($accountSummaries),
        ];
    }
}
