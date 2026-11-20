<?php

namespace App\Filament\Admin\Resources\PracetakPlateArchiveResource\Pages;

use App\Filament\Admin\Resources\PracetakPlateArchiveResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePracetakPlateArchive extends CreateRecord
{
    protected static string $resource = PracetakPlateArchiveResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
