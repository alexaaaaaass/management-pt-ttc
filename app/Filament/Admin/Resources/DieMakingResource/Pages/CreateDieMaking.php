<?php

namespace App\Filament\Admin\Resources\DieMakingResource\Pages;

use App\Filament\Admin\Resources\DieMakingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDieMaking extends CreateRecord
{
    protected static string $resource = DieMakingResource::class;

    protected function afterCreate(): void
    {
        $this->record->syncWasteSummary();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
