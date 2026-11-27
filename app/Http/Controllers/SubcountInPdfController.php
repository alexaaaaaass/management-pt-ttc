<?php

namespace App\Http\Controllers;

use App\Models\SubcountIn;
use Barryvdh\DomPDF\Facade\Pdf;

class SubcountInPdfController extends Controller
{
    protected function loadRelations(SubcountIn $subcountIn): SubcountIn
    {
        return $subcountIn->load([
            'subcountOut.supplier',
            'supplier',
            'items.subcountOut.supplier',
            'items.spk.salesOrder.itemable',
            'items.itemAsal',
            'items.itemHasil',
            'items.finishGoodItem',
        ]);
    }

    public function preview(SubcountIn $subcountIn)
    {
        $this->loadRelations($subcountIn);

        $pdf = Pdf::loadView('pdf.subcount-in', [
            'subcountIn' => $subcountIn,
        ]);

        $filename = str_replace('/', '-', (string) $subcountIn->no_subcount);

        return $pdf->stream($filename . '.pdf');
    }

    public function download(SubcountIn $subcountIn)
    {
        $this->loadRelations($subcountIn);

        $pdf = Pdf::loadView('pdf.subcount-in', [
            'subcountIn' => $subcountIn,
        ]);

        $filename = str_replace('/', '-', (string) $subcountIn->no_subcount);

        return $pdf->download($filename . '.pdf');
    }
}
