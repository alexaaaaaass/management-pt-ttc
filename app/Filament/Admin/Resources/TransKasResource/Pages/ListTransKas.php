<?php

namespace App\Filament\Admin\Resources\TransKasResource\Pages;

use App\Filament\Admin\Resources\AdminResource\Widgets\PemasukanChart;
use App\Filament\Admin\Resources\AdminResource\Widgets\PengeluaranChart;
use App\Filament\Admin\Resources\TransKasResource;
use App\Filament\Admin\Widgets\StatsOverview;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTransKas extends ListRecords
{
    protected static string $resource = TransKasResource::class;

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
            'KAS_MASUK' => 'Transaksi Kas Masuk',
            'KAS_KELUAR' => 'Transaksi Kas Keluar',
            default => 'Transaksi Kas Masuk & Kas Keluar',
        };
    }

    public function getTabs(): array
    {
        return [
            'ALL' => Tab::make('Semua Transaksi Kas'),
            'KAS_MASUK' => Tab::make('Transaksi Kas Masuk')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe_transaksi', 'KAS_MASUK')),
            'KAS_KELUAR' => Tab::make('Transaksi Kas Keluar')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe_transaksi', 'KAS_KELUAR')),
        ];
    }
}
