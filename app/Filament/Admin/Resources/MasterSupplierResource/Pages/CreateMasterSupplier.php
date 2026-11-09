<?php

namespace App\Filament\Admin\Resources\MasterSupplierResource\Pages;

use App\Filament\Admin\Resources\MasterSupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterSupplier extends CreateRecord
{
    protected static string $resource = MasterSupplierResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
