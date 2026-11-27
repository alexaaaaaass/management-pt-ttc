<?php

namespace App\Filament\Admin\Pages;

use App\Services\Finance\FinancialReportService;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class LaporanHutangPiutang extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Report';
    protected static ?string $navigationLabel = 'Hutang & Piutang';
    protected static ?string $title = 'Report Finance — Kartu Hutang, Piutang & Pinjaman';
    protected static ?int $navigationSort = 4;
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.admin.pages.laporan-hutang-piutang';

    public string $asOfDate = '';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function mount(): void
    {
        $this->asOfDate = (string) request()->query('as_of_date', now()->toDateString());
    }

    public function setPresetDate(string $preset): void
    {
        $this->asOfDate = match ($preset) {
            'today'        => now()->toDateString(),
            'end_of_month' => now()->endOfMonth()->toDateString(),
            'last_month'   => now()->subMonth()->endOfMonth()->toDateString(),
            'end_of_year'  => now()->endOfYear()->toDateString(),
            default        => $this->asOfDate,
        };
    }

    public function getReportDataProperty(): array
    {
        return app(FinancialReportService::class)->getDebtReceivableReport($this->asOfDate);
    }
}
