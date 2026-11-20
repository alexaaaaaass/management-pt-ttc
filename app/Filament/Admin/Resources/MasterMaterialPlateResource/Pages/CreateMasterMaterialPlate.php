<?php

namespace App\Filament\Admin\Resources\MasterMaterialPlateResource\Pages;

use App\Filament\Admin\Resources\MasterMaterialPlateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterMaterialPlate extends CreateRecord
{
    protected static string $resource = MasterMaterialPlateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
