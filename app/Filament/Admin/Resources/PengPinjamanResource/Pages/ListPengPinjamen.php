<?php

namespace App\Filament\Admin\Resources\PengPinjamanResource\Pages;

use App\Filament\Admin\Resources\PengPinjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengPinjamen extends ListRecords
{
    protected static string $resource = PengPinjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
