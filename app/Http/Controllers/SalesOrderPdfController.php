<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesOrderPdfController extends Controller
{
    public function preview(SalesOrder $salesOrder)
    {
        $pdf = Pdf::loadView(
            'pdf.sales-order',
            compact('salesOrder')
        );

        return $pdf->stream(
            'SalesOrder-'.$salesOrder->no_sales_order.'.pdf'
        );
    }

    public function download(SalesOrder $salesOrder)
    {
        $pdf = Pdf::loadView(
            'pdf.sales-order',
            compact('salesOrder')
        );

        return $pdf->download(
            'SalesOrder-'.$salesOrder->no_sales_order.'.pdf'
        );
    }
}