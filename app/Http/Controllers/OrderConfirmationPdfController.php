<?php

namespace App\Http\Controllers;

use App\Models\OrderConfirmation;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderConfirmationPdfController extends Controller
{
    protected function getSafeFilename(OrderConfirmation $orderConfirmation): string
    {
        $sanitized = str_replace(['/', '\\'], '-', $orderConfirmation->doc_no);
        return 'OrderConfirmation-' . $sanitized . '.pdf';
    }

    public function preview(OrderConfirmation $orderConfirmation)
    {
        $orderConfirmation->loadMissing(['customer', 'items.finishGoodItem', 'approvalForm']);

        $pdf = Pdf::loadView('pdf.order-confirmation', compact('orderConfirmation'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream($this->getSafeFilename($orderConfirmation));
    }

    public function download(OrderConfirmation $orderConfirmation)
    {
        $orderConfirmation->loadMissing(['customer', 'items.finishGoodItem', 'approvalForm']);

        $pdf = Pdf::loadView('pdf.order-confirmation', compact('orderConfirmation'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($this->getSafeFilename($orderConfirmation));
    }
}
