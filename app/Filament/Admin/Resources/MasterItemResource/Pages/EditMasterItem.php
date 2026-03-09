<?php

namespace App\Filament\Admin\Resources\MasterItemResource\Pages;

use App\Filament\Admin\Resources\MasterItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterItem extends EditRecord
{
    protected static string $resource = MasterItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
