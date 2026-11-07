<?php

namespace App\Filament\Admin\Resources\FinishGoodLedgerResource\Pages;

use App\Filament\Admin\Resources\FinishGoodLedgerResource;
use Filament\Resources\Pages\ListRecords;

class ListFinishGoodLedgers extends ListRecords
{
    protected static string $resource = FinishGoodLedgerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

