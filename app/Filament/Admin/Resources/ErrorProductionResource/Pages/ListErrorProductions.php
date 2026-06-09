<?php

namespace App\Filament\Admin\Resources\ErrorProductionResource\Pages;

use App\Filament\Admin\Resources\ErrorProductionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListErrorProductions extends ListRecords
{
    protected static string $resource = ErrorProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
