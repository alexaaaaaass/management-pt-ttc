<?php

namespace App\Filament\Admin\Resources\SubcountInResource\Pages;

use App\Filament\Admin\Resources\SubcountInResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubcountIns extends ListRecords
{
    protected static string $resource = SubcountInResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
