<?php

namespace App\Filament\Admin\Resources\OperatorFinishingResource\Pages;

use App\Filament\Admin\Resources\OperatorFinishingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOperatorFinishing extends CreateRecord
{
    protected static string $resource = OperatorFinishingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
