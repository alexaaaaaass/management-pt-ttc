<?php

namespace App\Filament\Admin\Resources\PotTunjanganResource\Pages;

use App\Filament\Admin\Resources\PotTunjanganResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPotTunjangans extends ListRecords
{
    protected static string $resource = PotTunjanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
