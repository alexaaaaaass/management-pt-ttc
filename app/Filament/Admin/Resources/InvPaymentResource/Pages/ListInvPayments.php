<?php

namespace App\Filament\Admin\Resources\InvPaymentResource\Pages;

use App\Filament\Admin\Resources\InvPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvPayments extends ListRecords
{
    protected static string $resource = InvPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
