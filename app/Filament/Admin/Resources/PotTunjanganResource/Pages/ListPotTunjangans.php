<?php

namespace App\Filament\Admin\Resources\PotTunjanganResource\Pages;

use App\Filament\Admin\Resources\BonusKaryawanResource;
use App\Filament\Admin\Resources\GajiResource;
use App\Filament\Admin\Resources\PotTunjanganResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPotTunjangans extends ListRecords
{
    protected static string $resource = PotTunjanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backGaji')
                ->label('← Kembali ke Penggajian')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => GajiResource::getUrl('index')),

            Actions\Action::make('goBonus')
                ->label('Bonus Karyawan')
                ->icon('heroicon-o-gift')
                ->color('success')
                ->url(fn () => BonusKaryawanResource::getUrl('index')),

            Actions\CreateAction::make()
                ->label('+ Tambah Potongan')
                ->modalHeading('Tambah Potongan Tunjangan')
                ->modalSubmitActionLabel('Simpan'),
        ];
    }
}