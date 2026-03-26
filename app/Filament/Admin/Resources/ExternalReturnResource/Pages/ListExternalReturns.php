<?php

namespace App\Filament\Admin\Resources\ExternalReturnResource\Pages;

use App\Filament\Admin\Resources\ExternalReturnResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExternalReturns extends ListRecords
{
    protected static string $resource = ExternalReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
