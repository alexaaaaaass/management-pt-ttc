<?php

namespace App\Filament\Admin\Resources\TransKasBankResource\Pages;

use App\Filament\Admin\Resources\TransKasBankResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransKasBanks extends ListRecords
{
    protected static string $resource = TransKasBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
