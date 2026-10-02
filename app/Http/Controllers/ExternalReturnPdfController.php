<?php

namespace App\Http\Controllers;

use App\Models\ExternalReturn;
use Barryvdh\DomPDF\Facade\Pdf;

class ExternalReturnPdfController extends Controller
{
    public function preview(ExternalReturn $externalReturn)
    {
        $externalReturn->load([
            'penerimaanBarang.purchaseOrder.supplier',
            'items.penerimaanBarangItem.purchaseOrderItem.item.satuan',
        ]);

        return Pdf::loadView(
            'pdf.external-return',
            compact('externalReturn')
        )->stream();
    }

    public function download(ExternalReturn $externalReturn)
    {
        $externalReturn->load([
            'penerimaanBarang.purchaseOrder.supplier',
            'items.penerimaanBarangItem.purchaseOrderItem.item.satuan',
        ]);

        return Pdf::loadView(
            'pdf.external-return',
            compact('externalReturn')
        )->download($externalReturn->no_return . '.pdf');
    }
}