<?php

namespace App\Filament\Admin\Pages;

use App\Models\MasterPrinting;
use App\Services\Finance\FinancialReportService;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class LaporanLabaRugiMesin extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationGroup = 'Report';
    protected static ?string $navigationLabel = 'Laba Rugi (Mesin)';
    protected static ?string $title = 'Report Finance — Laba Rugi (Mesin)';
    protected static ?int $navigationSort = 2;
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.admin.pages.laporan-laba-rugi-mesin';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public string $startDate = '';
    public string $endDate = '';
    public string $selectedMesin = '';
    public string $viewMode = 'ALL'; // ALL, SUMMARY, DETAIL

    public function mount(): void
    {
        $this->startDate = request()->query('start_date', now()->startOfYear()->toDateString());
        $this->endDate = request()->query('end_date', now()->endOfMonth()->toDateString());
        $this->selectedMesin = request()->query('mesin', '');
    }

    public function setPreset(string $preset): void
    {
        match ($preset) {
            'this_month' => [
                $this->startDate = now()->startOfMonth()->toDateString(),
                $this->endDate = now()->endOfMonth()->toDateString(),
            ],
            'last_month' => [
                $this->startDate = now()->subMonth()->startOfMonth()->toDateString(),
                $this->endDate = now()->subMonth()->endOfMonth()->toDateString(),
            ],
            'this_year' => [
                $this->startDate = now()->startOfYear()->toDateString(),
                $this->endDate = now()->endOfYear()->toDateString(),
            ],
            default => null,
        };
    }

    public function getMesinListProperty(): array
    {
        return MasterPrinting::orderBy('nama_mesin')
            ->pluck('nama_mesin', 'nama_mesin')
            ->toArray();
    }

    public function getReportDataProperty(): array
    {
        $data = app(FinancialReportService::class)->getMachineProfitabilityReport($this->startDate, $this->endDate);

        if ($this->selectedMesin !== '') {
            $filtered = array_values(array_filter(
                $data['machines'],
                fn (array $m) => strcasecmp((string) ($m['nama_mesin'] ?? ''), $this->selectedMesin) === 0
                    || strcasecmp((string) ($m['kode_mesin'] ?? ''), $this->selectedMesin) === 0
            ));

            $grandPendapatan = array_sum(array_column($filtered, 'pendapatan'));
            $grandBahan      = array_sum(array_column($filtered, 'biaya_bahan'));
            $grandOp         = array_sum(array_column($filtered, 'biaya_operasional'));
            $grandTotalBiaya = $grandBahan + $grandOp;
            $grandLaba       = $grandPendapatan - $grandTotalBiaya;

            $data['machines']                = $filtered;
            $data['grand_spk_count']         = array_sum(array_column($filtered, 'jumlah_spk'));
            $data['grand_hasil_baik']        = array_sum(array_column($filtered, 'hasil_baik'));
            $data['grand_hasil_rusak']       = array_sum(array_column($filtered, 'hasil_rusak'));
            $data['grand_pendapatan']        = $grandPendapatan;
            $data['grand_biaya_bahan']       = $grandBahan;
            $data['grand_biaya_operasional'] = $grandOp;
            $data['grand_total_biaya']       = $grandTotalBiaya;
            $data['grand_laba']              = $grandLaba;
            $data['grand_margin_persen']     = $grandPendapatan > 0
                ? round(($grandLaba / $grandPendapatan) * 100, 2)
                : 0.0;
        }

        return $data;
    }
}
