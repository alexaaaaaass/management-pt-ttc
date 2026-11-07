<?php

namespace App\Filament\Admin\Resources\CutiResource\Pages;

use App\Filament\Admin\Resources\CutiResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCuti extends CreateRecord
{
    protected static string $resource = CutiResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
