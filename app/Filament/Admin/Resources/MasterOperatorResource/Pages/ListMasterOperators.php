<?php

namespace App\Filament\Admin\Resources\MasterOperatorResource\Pages;

use App\Filament\Admin\Resources\MasterOperatorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterOperators extends ListRecords
{
    protected static string $resource = MasterOperatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
