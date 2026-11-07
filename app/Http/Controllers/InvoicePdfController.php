<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoicePdfController extends Controller
{
    protected function preparePdf(Invoice $invoice)
    {
        $invoice->loadMissing([
            'items',
            'payments.metodeBayar',
            'customer',
            'salesOrder.customer',
            'salesOrder.itemable',
            'salesOrder.items.material',
            'suratJalan.salesOrder.customer',
            'suratJalan.salesOrder.itemable',
            'suratJalan.salesOrder.items.material',
            'suratJalan.spk.packagings',
        ]);

        $logoPath = public_path('images/Logo PT Teknografi.png');
        if (!file_exists($logoPath)) {
            $logoPath = public_path('images/logo.png');
        }

        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'logoBase64' => $logoBase64,
        ]);

        $pdf->setPaper('A4', 'landscape');

        return $pdf;
    }

    public function preview(Invoice $invoice)
    {
        $pdf = $this->preparePdf($invoice);
        $filename = 'Invoice_' . str_replace(['/', '\\'], '_', $invoice->kode_invoice) . '.pdf';
        return $pdf->stream($filename);
    }

    public function download(Invoice $invoice)
    {
        $pdf = $this->preparePdf($invoice);
        $filename = 'Invoice_' . str_replace(['/', '\\'], '_', $invoice->kode_invoice) . '.pdf';
        return $pdf->download($filename);
    }
}
