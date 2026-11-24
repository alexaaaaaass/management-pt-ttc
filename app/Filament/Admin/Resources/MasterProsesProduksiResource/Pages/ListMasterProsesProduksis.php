<?php

namespace App\Filament\Admin\Resources\MasterProsesProduksiResource\Pages;

use App\Filament\Admin\Resources\MasterProsesProduksiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterProsesProduksis extends ListRecords
{
    protected static string $resource = MasterProsesProduksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
