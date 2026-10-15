<?php

namespace App\Filament\Admin\Resources\InvPaymentResource\Pages;

use App\Filament\Admin\Resources\InvPaymentResource;
use App\Services\Finance\InvoicePaymentService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditInvPayment extends EditRecord
{
    protected static string $resource = InvPaymentResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(InvoicePaymentService::class)->updatePayment($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
