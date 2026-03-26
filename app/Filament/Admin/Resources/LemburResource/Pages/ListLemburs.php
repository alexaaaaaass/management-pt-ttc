<?php

namespace App\Filament\Admin\Resources\LemburResource\Pages;

use App\Filament\Admin\Resources\LemburResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLemburs extends ListRecords
{
    protected static string $resource = LemburResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
