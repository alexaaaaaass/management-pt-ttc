<?php

namespace App\Filament\Admin\Resources\OrderConfirmationResource\Pages;

use App\Filament\Admin\Resources\OrderConfirmationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderConfirmations extends ListRecords
{
    protected static string $resource = OrderConfirmationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Order Confirmation'),
        ];
    }
}
