<?php

namespace App\Filament\Admin\Resources\OperatorDiemakingResource\Pages;

use App\Filament\Admin\Resources\OperatorDiemakingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOperatorDiemakings extends ListRecords
{
    protected static string $resource = OperatorDiemakingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
