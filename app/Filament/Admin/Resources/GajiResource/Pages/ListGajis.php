<?php

namespace App\Filament\Admin\Resources\GajiResource\Pages;

use App\Filament\Admin\Resources\GajiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGajis extends ListRecords
{
    protected static string $resource = GajiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
