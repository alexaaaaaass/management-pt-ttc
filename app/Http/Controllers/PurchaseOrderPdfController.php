<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrderPdfController extends Controller
{
    public function preview(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(
            'supplier',
            'purchaseRequest',
            'items.item'
        );

        $pdf = Pdf::loadView(
            'pdf.purchase-order',
            [
                'po' => $purchaseOrder
            ]
        );

        return $pdf->stream(
            $purchaseOrder->no_po.'.pdf'
        );
    }

    public function download(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(
            'supplier',
            'purchaseRequest',
            'items.item'
        );

        $pdf = Pdf::loadView(
            'pdf.purchase-order',
            [
                'po' => $purchaseOrder
            ]
        );

        return $pdf->download(
            $purchaseOrder->no_po.'.pdf'
        );
    }
}