<?php

namespace App\Filament\Admin\Resources\PracetakPenerimaanResource\Pages;

use App\Filament\Admin\Resources\PracetakPenerimaanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPracetakPenerimaans extends ListRecords
{
    protected static string $resource = PracetakPenerimaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Penerimaan Baru'),
        ];
    }
}
