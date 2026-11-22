<?php

namespace App\Filament\Admin\Resources\MasterMaterialPlateResource\Pages;

use App\Filament\Admin\Resources\MasterMaterialPlateResource;
use App\Filament\Admin\Resources\PracetakMaterialMovementResource;
use App\Filament\Admin\Resources\PracetakPenerimaanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterMaterialPlates extends ListRecords
{
    protected static string $resource = MasterMaterialPlateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('histori_ledger')
                ->label('Buku Besar Mutasi (Ledger)')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->url(PracetakMaterialMovementResource::getUrl('index')),

            Actions\Action::make('penerimaan_baru')
                ->label('Penerimaan Plate (IN)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(PracetakPenerimaanResource::getUrl('create')),

            Actions\CreateAction::make()
                ->label('Tambah Material Plate'),
        ];
    }
}
