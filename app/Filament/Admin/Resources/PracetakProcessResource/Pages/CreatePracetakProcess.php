<?php

namespace App\Filament\Admin\Resources\PracetakProcessResource\Pages;

use App\Filament\Admin\Resources\PracetakProcessResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePracetakProcess extends CreateRecord
{
    protected static string $resource = PracetakProcessResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
