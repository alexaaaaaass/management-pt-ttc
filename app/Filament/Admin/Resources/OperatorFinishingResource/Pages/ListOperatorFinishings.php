<?php

namespace App\Filament\Admin\Resources\OperatorFinishingResource\Pages;

use App\Filament\Admin\Resources\OperatorFinishingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOperatorFinishings extends ListRecords
{
    protected static string $resource = OperatorFinishingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
