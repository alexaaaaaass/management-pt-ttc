<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesOrderPdfController extends Controller
{
    protected function getSafeFilename(SalesOrder $salesOrder): string
    {
        $sanitized = str_replace(['/', '\\'], '-', $salesOrder->no_sales_order);
        return 'SalesOrder-' . $sanitized . '.pdf';
    }

    public function preview(SalesOrder $salesOrder)
    {
        $salesOrder->loadMissing(['customer', 'itemable', 'items.material']);

        $pdf = Pdf::loadView(
            'pdf.sales-order',
            compact('salesOrder')
        );

        return $pdf->stream($this->getSafeFilename($salesOrder));
    }

    public function download(SalesOrder $salesOrder)
    {
        $salesOrder->loadMissing(['customer', 'itemable', 'items.material']);

        $pdf = Pdf::loadView(
            'pdf.sales-order',
            compact('salesOrder')
        );

        return $pdf->download($this->getSafeFilename($salesOrder));
    }
}