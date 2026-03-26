<?php

namespace App\Filament\Admin\Resources\PoBillingResource\Pages;

use App\Filament\Admin\Resources\PoBillingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPoBillings extends ListRecords
{
    protected static string $resource = PoBillingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
