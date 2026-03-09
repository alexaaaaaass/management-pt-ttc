<?php

namespace App\Filament\Admin\Resources\MasterKonversiResource\Pages;

use App\Filament\Admin\Resources\MasterKonversiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterKonversi extends EditRecord
{
    protected static string $resource = MasterKonversiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
