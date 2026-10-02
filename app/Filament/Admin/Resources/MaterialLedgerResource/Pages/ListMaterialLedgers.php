<?php

namespace App\Filament\Admin\Resources\MaterialLedgerResource\Pages;

use Filament\Resources\Pages\ListRecords;
use App\Filament\Admin\Resources\MaterialLedgerResource;

class ListMaterialLedgers extends ListRecords
{
    protected static string $resource = MaterialLedgerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
{
    return [
        \App\Filament\Admin\Widgets\MaterialLedgerOverview::class,
    ];
}
}