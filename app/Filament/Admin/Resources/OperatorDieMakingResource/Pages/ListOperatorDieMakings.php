<?php

namespace App\Filament\Admin\Resources\OperatorDieMakingResource\Pages;

use App\Filament\Admin\Resources\OperatorDieMakingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOperatorDieMakings extends ListRecords
{
    protected static string $resource = OperatorDieMakingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}