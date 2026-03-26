<?php

namespace App\Filament\Admin\Resources\PoBillPayResource\Pages;

use App\Filament\Admin\Resources\PoBillPayResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPoBillPays extends ListRecords
{
    protected static string $resource = PoBillPayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
