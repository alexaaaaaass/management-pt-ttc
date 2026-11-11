<?php

namespace App\Filament\Admin\Pages;

use App\Services\Finance\FinancialReportService;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class LaporanLabaRugi extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Report';
    protected static ?string $navigationLabel = 'Report Finance';
    protected static ?string $title = 'Report Finance — Laba Rugi (Global)';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.admin.pages.laporan-laba-rugi';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public string $startDate = '';
    public string $endDate = '';

    public function mount(): void
    {
        $this->startDate = request()->query('start_date', now()->startOfYear()->toDateString());
        $this->endDate = request()->query('end_date', now()->endOfMonth()->toDateString());
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
            'this_quarter' => [
                $this->startDate = now()->firstOfQuarter()->toDateString(),
                $this->endDate = now()->lastOfQuarter()->toDateString(),
            ],
            'this_year' => [
                $this->startDate = now()->startOfYear()->toDateString(),
                $this->endDate = now()->endOfYear()->toDateString(),
            ],
            default => null,
        };
    }

    public function getReportDataProperty(): array
    {
        return app(FinancialReportService::class)->getIncomeStatement($this->startDate, $this->endDate);
    }
}
