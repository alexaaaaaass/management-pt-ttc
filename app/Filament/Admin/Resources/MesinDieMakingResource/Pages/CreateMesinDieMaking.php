<?php

namespace App\Filament\Admin\Resources\MesinDieMakingResource\Pages;

use App\Filament\Admin\Resources\MesinDieMakingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMesinDieMaking extends CreateRecord
{
    protected static string $resource = MesinDieMakingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
