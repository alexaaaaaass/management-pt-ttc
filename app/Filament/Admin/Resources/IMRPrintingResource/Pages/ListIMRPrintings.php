<?php

namespace App\Filament\Admin\Resources\IMRPrintingResource\Pages;

use App\Filament\Admin\Resources\IMRPrintingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIMRPrintings extends ListRecords
{
    protected static string $resource = IMRPrintingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
