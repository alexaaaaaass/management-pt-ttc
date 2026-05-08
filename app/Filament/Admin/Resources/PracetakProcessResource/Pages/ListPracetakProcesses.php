<?php

namespace App\Filament\Admin\Resources\PracetakProcessResource\Pages;

use App\Filament\Admin\Resources\PracetakProcessResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPracetakProcesses extends ListRecords
{
    protected static string $resource = PracetakProcessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
