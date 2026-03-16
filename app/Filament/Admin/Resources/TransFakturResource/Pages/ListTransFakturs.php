<?php

namespace App\Filament\Admin\Resources\TransFakturResource\Pages;

use App\Filament\Admin\Resources\TransFakturResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransFakturs extends ListRecords
{
    protected static string $resource = TransFakturResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
