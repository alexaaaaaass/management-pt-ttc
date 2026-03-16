<?php

namespace App\Filament\Admin\Resources\MesinFinisResource\Pages;

use App\Filament\Admin\Resources\MesinFinisResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMesinFinis extends ListRecords
{
    protected static string $resource = MesinFinisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
