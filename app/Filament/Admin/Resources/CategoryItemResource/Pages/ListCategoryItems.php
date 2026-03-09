<?php

namespace App\Filament\Admin\Resources\CategoryItemResource\Pages;

use App\Filament\Admin\Resources\CategoryItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategoryItems extends ListRecords
{
    protected static string $resource = CategoryItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
