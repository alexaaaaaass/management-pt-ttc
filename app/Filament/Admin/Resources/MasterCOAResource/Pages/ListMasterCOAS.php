<?php

namespace App\Filament\Admin\Resources\MasterCOAResource\Pages;

use App\Filament\Admin\Resources\MasterCOAResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterCOAS extends ListRecords
{
    protected static string $resource = MasterCOAResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
