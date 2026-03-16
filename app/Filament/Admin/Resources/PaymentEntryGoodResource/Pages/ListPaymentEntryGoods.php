<?php

namespace App\Filament\Admin\Resources\PaymentEntryGoodResource\Pages;

use App\Filament\Admin\Resources\PaymentEntryGoodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentEntryGoods extends ListRecords
{
    protected static string $resource = PaymentEntryGoodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
