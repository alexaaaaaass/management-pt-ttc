<?php

namespace App\Filament\Admin\Resources\MasterProsesProduksiResource\Pages;

use App\Filament\Admin\Resources\MasterProsesProduksiResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterProsesProduksi extends CreateRecord
{
    protected static string $resource = MasterProsesProduksiResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
