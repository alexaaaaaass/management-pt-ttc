<?php

namespace App\Filament\Admin\Resources\MasterSatuanResource\Pages;

use App\Filament\Admin\Resources\MasterSatuanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterSatuan extends EditRecord
{
    protected static string $resource = MasterSatuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
