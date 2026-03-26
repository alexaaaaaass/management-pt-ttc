<?php

namespace App\Filament\Admin\Resources\SuratJalanResource\Pages;

use App\Filament\Admin\Resources\SuratJalanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSuratJalans extends ListRecords
{
    protected static string $resource = SuratJalanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
