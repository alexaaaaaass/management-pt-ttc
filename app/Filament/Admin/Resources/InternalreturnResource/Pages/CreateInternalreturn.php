<?php

namespace App\Filament\Admin\Resources\InternalreturnResource\Pages;

use App\Filament\Admin\Resources\InternalreturnResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInternalreturn extends CreateRecord
{
    protected static string $resource = InternalreturnResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (auth()->check()) {
            $data['created_by'] = auth()->id();
        }

        return $data;
    }
}

