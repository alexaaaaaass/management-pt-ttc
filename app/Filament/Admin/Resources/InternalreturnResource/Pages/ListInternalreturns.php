<?php

namespace App\Filament\Admin\Resources\InternalreturnResource\Pages;

use App\Filament\Admin\Resources\InternalreturnResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInternalreturns extends ListRecords
{
    protected static string $resource = InternalreturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
