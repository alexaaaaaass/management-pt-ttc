<?php

namespace App\Filament\Admin\Resources\MasterItemResource\Pages;

use App\Filament\Admin\Resources\MasterItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterItems extends ListRecords
{
    protected static string $resource = MasterItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
