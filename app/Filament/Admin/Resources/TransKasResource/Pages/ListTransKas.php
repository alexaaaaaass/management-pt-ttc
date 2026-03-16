<?php

namespace App\Filament\Admin\Resources\TransKasResource\Pages;

use App\Filament\Admin\Resources\TransKasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransKas extends ListRecords
{
    protected static string $resource = TransKasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
