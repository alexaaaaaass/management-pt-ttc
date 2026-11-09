<?php

namespace App\Filament\Admin\Resources\JurnalMemorialResource\Pages;

use App\Filament\Admin\Resources\JurnalMemorialResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJurnalMemorials extends ListRecords
{
    protected static string $resource = JurnalMemorialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Jurnal Memorial')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}

