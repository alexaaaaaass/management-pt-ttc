<?php

namespace App\Filament\Admin\Resources\InvPaymentResource\Pages;

use App\Models\Invoice;
use App\Filament\Admin\Resources\InvPaymentResource;

use Filament\Resources\Pages\CreateRecord;

class CreateInvPayment extends CreateRecord
{
    protected static string $resource =
        InvPaymentResource::class;

    protected function afterCreate(): void
    {
        $payment = $this->record;

        $invoice = Invoice::find(
            $payment->invoice_id
        );

        /*
        |--------------------------------------------------------------------------
        | TOTAL BAYAR
        |--------------------------------------------------------------------------
        */

        $totalBayar =
            $invoice->payments()
                ->sum('nominal_bayar');

        /*
        |--------------------------------------------------------------------------
        | SISA TAGIHAN
        |--------------------------------------------------------------------------
        */

        $sisa =
            $invoice->grand_total
            - $totalBayar;

        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        $invoice->update([

            'sisa_tagihan' => $sisa,

            'status' =>
                $sisa <= 0
                    ? 'LUNAS'
                    : 'BELUM LUNAS',
        ]);
    }
}