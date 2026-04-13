<?php

namespace App\Filament\Admin\Resources\PrintingResource\Pages;

use App\Filament\Admin\Resources\PrintingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrintings extends ListRecords
{
    protected static string $resource = PrintingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
