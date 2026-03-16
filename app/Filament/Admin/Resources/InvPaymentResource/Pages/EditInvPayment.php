<?php

namespace App\Filament\Admin\Resources\InvPaymentResource\Pages;

use App\Filament\Admin\Resources\InvPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvPayment extends EditRecord
{
    protected static string $resource = InvPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
