<?php

namespace App\Filament\Admin\Resources\MasterPrintingResource\Pages;

use App\Filament\Admin\Resources\MasterPrintingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterPrintings extends ListRecords
{
    protected static string $resource = MasterPrintingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
