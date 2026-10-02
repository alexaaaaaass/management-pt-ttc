<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\SuratJalan;

class InvoiceObserver
{
    /**
     * Saat create
     */
    public function creating(Invoice $invoice): void
    {
        $this->fillInvoice($invoice);
    }

    /**
     * Saat update
     */
    public function updating(Invoice $invoice): void
    {
        $this->fillInvoice($invoice);
    }

    /**
     * Semua proses invoice
     */
    protected function fillInvoice(Invoice $invoice): void
    {
        /*
        |--------------------------------------------------------------------------
        | Ambil Sales Order
        |--------------------------------------------------------------------------
        */

        $suratJalan = SuratJalan::with('salesOrder')
            ->find($invoice->surat_jalan_id);

        if ($suratJalan && $suratJalan->salesOrder) {

            $invoice->customer_id =
                $suratJalan->salesOrder->customer_id;

            $invoice->sales_order_id =
                $suratJalan->salesOrder->id;

            $invoice->no_po_customer =
                $suratJalan->salesOrder->no_po_customer;
        }

        /*
        |--------------------------------------------------------------------------
        | Hitung
        |--------------------------------------------------------------------------
        */

        $subtotal = (float) $invoice->subtotal;

        $diskon = (float) $invoice->diskon;

        $ppn = (float) $invoice->ppn;

        $ongkir = (float) $invoice->ongkir;

        $uangMuka = (float) $invoice->uang_muka;

        $diskonNominal =
            ($subtotal * $diskon) / 100;

        $ppnNominal =
            ($subtotal * $ppn) / 100;

        $grandTotal =
            $subtotal
            - $diskonNominal
            + $ppnNominal
            + $ongkir;

        $invoice->grand_total = $grandTotal;

        /*
        |--------------------------------------------------------------------------
        | Payment
        |--------------------------------------------------------------------------
        */

        $invoice->total_payment = $uangMuka;

        $invoice->sisa_tagihan =
            $grandTotal - $invoice->total_payment;

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if ($invoice->sisa_tagihan <= 0) {

            $invoice->status = 'Paid';

        } elseif ($invoice->total_payment > 0) {

            $invoice->status = 'Partial';

        } else {

            $invoice->status = 'Unpaid';
        }
    }
}