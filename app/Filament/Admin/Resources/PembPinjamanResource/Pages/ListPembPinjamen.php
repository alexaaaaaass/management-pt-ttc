<?php

namespace App\Filament\Admin\Resources\PembPinjamanResource\Pages;

use App\Filament\Admin\Resources\PembPinjamanResource;
use App\Filament\Admin\Resources\PengPinjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPembPinjamen extends ListRecords
{
    protected static string $resource = PembPinjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backPinjaman')
                ->label('← Pinjaman Karyawan')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => PengPinjamanResource::getUrl('index')),

            Actions\Action::make('rekap')
                ->label('Rekap Pinjaman')
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->url(fn () => PembPinjamanResource::getUrl('rekap')),

            Actions\CreateAction::make()
                ->label('+ Catat Cicilan Manual'),
        ];
    }
}