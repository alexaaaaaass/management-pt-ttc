<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoicePdfController extends Controller
{
    protected function preparePdf(Invoice $invoice)
    {
        $invoice->load(['items', 'suratJalan.salesOrder.customer', 'suratJalan.spk']);

$logoPath = public_path('images/Logo PT Teknografi.png');
$logoBase64 = '';

if (file_exists($logoPath)) {
    $logoData = file_get_contents($logoPath);
    $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
}
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'logoBase64' => $logoBase64,
        ]);

        $pdf->setPaper('A5', 'landscape');

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
