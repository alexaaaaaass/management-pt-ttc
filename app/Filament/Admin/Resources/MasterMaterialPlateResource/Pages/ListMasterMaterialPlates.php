<?php

namespace App\Filament\Admin\Resources\MasterMaterialPlateResource\Pages;

use App\Filament\Admin\Resources\MasterMaterialPlateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterMaterialPlates extends ListRecords
{
    protected static string $resource = MasterMaterialPlateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Material Plate'),
        ];
    }
}
