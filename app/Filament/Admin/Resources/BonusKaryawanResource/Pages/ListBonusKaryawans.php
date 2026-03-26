<?php

namespace App\Filament\Admin\Resources\BonusKaryawanResource\Pages;

use App\Filament\Admin\Resources\BonusKaryawanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBonusKaryawans extends ListRecords
{
    protected static string $resource = BonusKaryawanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
