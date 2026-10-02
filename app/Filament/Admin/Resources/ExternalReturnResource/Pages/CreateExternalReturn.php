<?php

namespace App\Filament\Admin\Resources\ExternalReturnResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Admin\Resources\ExternalReturnResource;

class CreateExternalReturn extends CreateRecord
{
    protected static string $resource = ExternalReturnResource::class;

 
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl(
            'edit',
            ['record'=>$this->record]
        );
    }
}