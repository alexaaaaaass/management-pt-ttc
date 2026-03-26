<?php

namespace App\Filament\Admin\Resources\CutiResource\Pages;

use App\Filament\Admin\Resources\CutiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCutis extends ListRecords
{
    protected static string $resource = CutiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
