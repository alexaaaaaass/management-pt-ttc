<?php

namespace App\Filament\Admin\Resources\MaterialLedgerResource\Pages;

use App\Filament\Admin\Resources\MaterialLedgerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMaterialLedger extends EditRecord
{
    protected static string $resource = MaterialLedgerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
