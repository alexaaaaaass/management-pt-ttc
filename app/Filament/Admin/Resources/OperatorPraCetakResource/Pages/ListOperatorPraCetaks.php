<?php

namespace App\Filament\Admin\Resources\OperatorPraCetakResource\Pages;

use App\Filament\Admin\Resources\OperatorPraCetakResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOperatorPraCetaks extends ListRecords
{
    protected static string $resource = OperatorPraCetakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
