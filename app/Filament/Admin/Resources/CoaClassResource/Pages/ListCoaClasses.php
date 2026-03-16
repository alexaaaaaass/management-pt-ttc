<?php

namespace App\Filament\Admin\Resources\CoaClassResource\Pages;

use App\Filament\Admin\Resources\CoaClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCoaClasses extends ListRecords
{
    protected static string $resource = CoaClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
