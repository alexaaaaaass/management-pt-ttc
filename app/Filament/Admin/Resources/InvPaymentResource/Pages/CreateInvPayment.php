<?php

namespace App\Filament\Admin\Resources\InvPaymentResource\Pages;

use App\Filament\Admin\Resources\InvoiceResource;
use App\Filament\Admin\Resources\InvPaymentResource;
use App\Services\Finance\InvoicePaymentService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateInvPayment extends CreateRecord
{
    protected static string $resource = InvPaymentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(InvoicePaymentService::class)->recordPayment($data);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pembayaran berhasil disimpan';
    }

    protected function getRedirectUrl(): string
    {
        return InvoiceResource::getUrl('view', [
            'record' => $this->record->invoice_id,
        ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($invoice = request('invoice')) {
            $data['invoice_id'] = $invoice;
        }

        return $data;
    }
}