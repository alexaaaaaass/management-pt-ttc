<?php

namespace App\Filament\Admin\Resources\PembPinjamanResource\Pages;

use App\Filament\Admin\Resources\PembPinjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPembPinjamen extends ListRecords
{
    protected static string $resource = PembPinjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
