<?php

namespace App\Filament\Admin\Resources\BonusKaryawanResource\Pages;

use App\Filament\Admin\Resources\BonusKaryawanResource;
use App\Filament\Admin\Resources\GajiResource;
use App\Filament\Admin\Resources\PotTunjanganResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBonusKaryawans extends ListRecords
{
    protected static string $resource = BonusKaryawanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backGaji')
                ->label('← Kembali ke Penggajian')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => GajiResource::getUrl('index')),

            Actions\Action::make('goPotongan')
                ->label('Potongan Tunjangan')
                ->icon('heroicon-o-scissors')
                ->color('danger')
                ->url(fn () => PotTunjanganResource::getUrl('index')),

            Actions\CreateAction::make()
                ->label('+ Tambah Bonus'),
        ];
    }
}
