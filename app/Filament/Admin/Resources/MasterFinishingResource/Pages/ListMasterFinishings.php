<?php

namespace App\Filament\Admin\Resources\MasterFinishingResource\Pages;

use App\Filament\Admin\Resources\MasterFinishingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterFinishings extends ListRecords
{
    protected static string $resource = MasterFinishingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
