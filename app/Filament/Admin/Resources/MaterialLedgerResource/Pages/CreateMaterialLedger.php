<?php

namespace App\Filament\Admin\Resources\MaterialLedgerResource\Pages;

use App\Filament\Admin\Resources\MaterialLedgerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMaterialLedger extends CreateRecord
{
    protected static string $resource = MaterialLedgerResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
