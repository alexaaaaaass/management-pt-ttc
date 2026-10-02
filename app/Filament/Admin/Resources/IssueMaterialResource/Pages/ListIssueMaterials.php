<?php

namespace App\Filament\Admin\Resources\IssueMaterialResource\Pages;

use App\Filament\Admin\Resources\IssueMaterialResource;
use Filament\Resources\Pages\ListRecords;

class ListIssueMaterials extends ListRecords
{
    protected static string $resource = IssueMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}