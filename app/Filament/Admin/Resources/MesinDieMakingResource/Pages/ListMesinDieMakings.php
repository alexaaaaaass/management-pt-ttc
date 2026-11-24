<?php

namespace App\Filament\Admin\Resources\MesinDieMakingResource\Pages;

use App\Filament\Admin\Resources\MesinDieMakingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMesinDieMakings extends ListRecords
{
    protected static string $resource = MesinDieMakingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
