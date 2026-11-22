<?php

namespace App\Filament\Admin\Resources\TransKasBankResource\Pages;

use App\Filament\Admin\Resources\AdminResource\Widgets\PemasukanChart;
use App\Filament\Admin\Resources\AdminResource\Widgets\PengeluaranChart;
use App\Filament\Admin\Resources\TransKasBankResource;
use App\Filament\Admin\Widgets\StatsOverview;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTransKasBanks extends ListRecords
{
    protected static string $resource = TransKasBankResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
            PemasukanChart::class,
            PengeluaranChart::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | string | array
    {
        return 2;
    }

    public function getHeading(): string
    {
        return match ($this->activeTab) {
            'BANK_MASUK' => 'Transaksi Bank Masuk',
            'BANK_KELUAR' => 'Transaksi Bank Keluar',
            default => 'Transaksi Bank Masuk & Bank Keluar',
        };
    }

    public function getTabs(): array
    {
        return [
            'ALL' => Tab::make('Semua Transaksi Bank'),
            'BANK_MASUK' => Tab::make('Transaksi Bank Masuk')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe_transaksi', 'BANK_MASUK')),
            'BANK_KELUAR' => Tab::make('Transaksi Bank Keluar')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe_transaksi', 'BANK_KELUAR')),
        ];
    }
}
