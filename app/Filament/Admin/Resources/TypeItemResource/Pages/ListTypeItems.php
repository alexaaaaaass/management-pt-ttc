<?php

namespace App\Filament\Admin\Resources\TypeItemResource\Pages;

use App\Filament\Admin\Resources\TypeItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTypeItems extends ListRecords
{
    protected static string $resource = TypeItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
