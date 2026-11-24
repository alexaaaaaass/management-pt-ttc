<?php

namespace App\Filament\Admin\Resources\DieMakingResource\Pages;

use App\Filament\Admin\Resources\DieMakingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDieMakings extends ListRecords
{
    protected static string $resource = DieMakingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
