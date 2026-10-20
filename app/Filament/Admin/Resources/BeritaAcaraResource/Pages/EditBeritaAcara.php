<?php

namespace App\Filament\Admin\Resources\BeritaAcaraResource\Pages;

use App\Filament\Admin\Resources\BeritaAcaraResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBeritaAcara extends EditRecord
{
    protected static string $resource = BeritaAcaraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}