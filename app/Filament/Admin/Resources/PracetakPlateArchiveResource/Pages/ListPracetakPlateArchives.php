<?php

namespace App\Filament\Admin\Resources\PracetakPlateArchiveResource\Pages;

use App\Filament\Admin\Resources\PracetakPlateArchiveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPracetakPlateArchives extends ListRecords
{
    protected static string $resource = PracetakPlateArchiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Arsip Plate Manual'),
        ];
    }
}
