<?php

namespace App\Filament\Admin\Resources\PenerimaanBarangResource\Pages;

use App\Filament\Admin\Resources\PenerimaanBarangResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePenerimaanBarang extends CreateRecord
{
    protected static string $resource = PenerimaanBarangResource::class;

    protected function afterCreate(): void
    {
        $this->record->processReceiving();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
