<?php

namespace App\Filament\Admin\Pages;

use App\Services\Finance\FinancialReportService;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class LaporanNeraca extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationGroup = 'Report';
    protected static ?string $navigationLabel = 'Neraca';
    protected static ?string $title = 'Report Finance — Laporan Neraca';
    protected static ?int $navigationSort = 3;
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.admin.pages.laporan-neraca';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public string $asOfDate = '';

    public function mount(): void
    {
        $this->asOfDate = request()->query('as_of_date', now()->toDateString());
    }

    public function setPresetDate(string $preset): void
    {
        $this->asOfDate = match ($preset) {
            'today' => now()->toDateString(),
            'end_of_month' => now()->endOfMonth()->toDateString(),
            'last_month' => now()->subMonth()->endOfMonth()->toDateString(),
            'end_of_year' => now()->endOfYear()->toDateString(),
            default => $this->asOfDate,
        };
    }

    public function getReportDataProperty(): array
    {
        return app(FinancialReportService::class)->getBalanceSheet($this->asOfDate);
    }
}
