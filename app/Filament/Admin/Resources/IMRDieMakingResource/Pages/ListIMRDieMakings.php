<?php

namespace App\Filament\Admin\Resources\IMRDieMakingResource\Pages;

use App\Filament\Admin\Resources\IMRDieMakingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIMRDieMakings extends ListRecords
{
    protected static string $resource = IMRDieMakingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat IMR Die Making'),
        ];
    }
}
