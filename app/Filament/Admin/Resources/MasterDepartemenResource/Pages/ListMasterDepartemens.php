<?php

namespace App\Filament\Admin\Resources\MasterDepartemenResource\Pages;

use App\Filament\Admin\Resources\MasterDepartemenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterDepartemens extends ListRecords
{
    protected static string $resource = MasterDepartemenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
