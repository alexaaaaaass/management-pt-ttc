<?php

namespace App\Filament\Admin\Resources\NpbRequestResource\Pages;

use App\Filament\Admin\Resources\NpbRequestResource;
use App\Services\Finance\NpbService;
use Filament\Resources\Pages\CreateRecord;

class CreateNpbRequest extends CreateRecord
{
    protected static string $resource = NpbRequestResource::class;

    protected function afterCreate(): void
    {
        app(NpbService::class)->recalculateRequest($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
