<?php

namespace App\Filament\Admin\Resources\MetodeBayarResource\Pages;

use App\Filament\Admin\Resources\MetodeBayarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMetodeBayars extends ListRecords
{
    protected static string $resource = MetodeBayarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
