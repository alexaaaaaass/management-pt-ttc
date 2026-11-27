<?php

namespace App\Filament\Admin\Resources\PengPinjamanResource\Pages;

use App\Filament\Admin\Resources\PembPinjamanResource;
use App\Filament\Admin\Resources\PengPinjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengPinjamen extends ListRecords
{
    protected static string $resource = PengPinjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('riwayatCicilan')
                ->label('Semua Riwayat Cicilan')
                ->icon('heroicon-o-credit-card')
                ->color('info')
                ->url(fn () => PembPinjamanResource::getUrl('index')),

            Actions\Action::make('rekapPdf')
                ->label('PDF Rekap Pinjaman')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('hrd.pinjaman.rekap.preview'))
                ->openUrlInNewTab(),

            Actions\CreateAction::make()
                ->label('+ Pengajuan Pinjaman'),
        ];
    }
}
