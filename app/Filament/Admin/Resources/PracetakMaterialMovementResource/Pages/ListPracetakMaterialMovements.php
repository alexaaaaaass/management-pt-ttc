<?php

namespace App\Filament\Admin\Resources\PracetakMaterialMovementResource\Pages;

use App\Filament\Admin\Resources\PracetakMaterialMovementResource;
use Filament\Resources\Pages\ListRecords;

class ListPracetakMaterialMovements extends ListRecords
{
    protected static string $resource = PracetakMaterialMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
