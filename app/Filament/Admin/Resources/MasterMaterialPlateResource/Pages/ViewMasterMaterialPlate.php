<?php

namespace App\Filament\Admin\Resources\MasterMaterialPlateResource\Pages;

use App\Filament\Admin\Resources\MasterMaterialPlateResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMasterMaterialPlate extends ViewRecord
{
    protected static string $resource = MasterMaterialPlateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
