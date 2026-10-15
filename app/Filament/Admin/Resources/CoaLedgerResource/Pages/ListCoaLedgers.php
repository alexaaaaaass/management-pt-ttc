<?php

namespace App\Filament\Admin\Resources\CoaLedgerResource\Pages;

use App\Filament\Admin\Resources\CoaLedgerResource;
use Filament\Resources\Pages\ListRecords;

class ListCoaLedgers extends ListRecords
{
    protected static string $resource = CoaLedgerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
