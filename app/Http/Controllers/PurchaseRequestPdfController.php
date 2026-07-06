<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseRequestPdfController extends Controller
{
    public function preview(PurchaseRequest $purchaseRequest)
    {
        $pdf = Pdf::loadView(
            'pdf.purchase-request',
            [
                'pr' => $purchaseRequest->load(
                    'departemen',
                    'items.item'
                )
            ]
        );

        return $pdf->stream(
            $purchaseRequest->nomor_pr.'.pdf'
        );
    }

    public function download(PurchaseRequest $purchaseRequest)
    {
        $pdf = Pdf::loadView(
            'pdf.purchase-request',
            [
                'pr' => $purchaseRequest->load(
                    'departemen',
                    'items.item'
                )
            ]
        );

        return $pdf->download(
            $purchaseRequest->nomor_pr.'.pdf'
        );
    }
}