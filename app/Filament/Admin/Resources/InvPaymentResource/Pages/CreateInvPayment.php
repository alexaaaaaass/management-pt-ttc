<?php

namespace App\Filament\Admin\Resources\InvPaymentResource\Pages;

use App\Filament\Admin\Resources\InvoiceResource;
use App\Filament\Admin\Resources\InvPaymentResource;
use App\Models\Invoice;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateInvPayment extends CreateRecord
{
    protected static string $resource =
        InvPaymentResource::class;

   protected function afterCreate(): void
{
    // $payment = $this->record;

    // $invoice = Invoice::find(
    //     $payment->invoice_id
    // );

    // if (!$invoice) {
    //     return;
    // }

    // /*
    // |--------------------------------------------------------------------------
    // | TOTAL PAYMENT
    // |--------------------------------------------------------------------------
    // */

    // $totalPayment = $invoice
    //     ->payments()
    //     ->sum('nominal_bayar');

    // /*
    // |--------------------------------------------------------------------------
    // | SISA TAGIHAN
    // |--------------------------------------------------------------------------
    // */

    // $sisa =
    //     $invoice->grand_total
    //     - $totalPayment;

    // /*
    // |--------------------------------------------------------------------------
    // | STATUS
    // |--------------------------------------------------------------------------
    // */

    // if ($totalPayment <= 0) {

    //     $status = 'Unpaid';

    // } elseif ($totalPayment >= $invoice->grand_total) {

    //     $status = 'Paid';

    //     $sisa = 0;

    // } else {

    //     $status = 'Partial';

    // }

    // /*
    // |--------------------------------------------------------------------------
    // | UPDATE INVOICE
    // |--------------------------------------------------------------------------
    // */

    // $invoice->update([

    //     'total_payment' => $totalPayment,

    //     'sisa_tagihan' => $sisa,

    //     'status' => $status,

    // ]);
}
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pembayaran berhasil disimpan';
    }

    protected function getRedirectUrl(): string
    {
        return InvoiceResource::getUrl(
            'view',
            [
                'record' => $this->record->invoice_id
            ]
        );
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($invoice = request('invoice')) {

            $data['invoice_id'] = $invoice;

        }

        return $data;
    }
}