<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\Finance\InvoiceService;

class InvoiceObserver
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Saat create
     */
    public function creating(Invoice $invoice): void
    {
        $this->invoiceService->syncInvoiceFinancials($invoice);
    }

    /**
     * Saat update
     */
    public function updating(Invoice $invoice): void
    {
        $this->invoiceService->syncInvoiceFinancials($invoice);
    }

    /**
     * Saat delete
     */
    public function deleting(Invoice $invoice): void
    {
        if ($invoice->payments()->exists()) {
            throw new \Exception('Tidak dapat menghapus Invoice karena sudah memiliki riwayat pembayaran.');
        }
    }
}