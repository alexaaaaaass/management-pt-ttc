<?php

namespace App\Filament\Admin\Resources\MasterMaterialPlateResource\Pages;

use App\Filament\Admin\Resources\MasterMaterialPlateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterMaterialPlate extends EditRecord
{
    protected static string $resource = MasterMaterialPlateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action) {
                    MasterMaterialPlateResource::guardDelete($this->getRecord(), $action);
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
