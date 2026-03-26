<?php

namespace App\Filament\Admin\Resources\SuratPerintahKerjaResource\Pages;

use App\Filament\Admin\Resources\SuratPerintahKerjaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSuratPerintahKerjas extends ListRecords
{
    protected static string $resource = SuratPerintahKerjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
