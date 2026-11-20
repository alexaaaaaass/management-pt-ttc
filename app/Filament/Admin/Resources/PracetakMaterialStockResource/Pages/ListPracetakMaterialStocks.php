<?php

namespace App\Filament\Admin\Resources\PracetakMaterialStockResource\Pages;

use App\Filament\Admin\Resources\PracetakMaterialStockResource;
use Filament\Resources\Pages\ListRecords;

class ListPracetakMaterialStocks extends ListRecords
{
    protected static string $resource = PracetakMaterialStockResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
