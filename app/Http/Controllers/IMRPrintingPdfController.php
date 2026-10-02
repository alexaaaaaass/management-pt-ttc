<?php

namespace App\Http\Controllers;

use App\Models\ImrPrinting;
use Barryvdh\DomPDF\Facade\Pdf;

class IMRPrintingPdfController extends Controller
{
    public function preview(ImrPrinting $imr)
    {
        $imr->load([
            'departemen',
            'spk',
            'items.item.satuan',
        ]);

        $pdf = Pdf::loadView(
            'pdf.imr-preview',
            compact('imr')
        );

        return $pdf->stream(
            $imr->no_imr . '.pdf'
        );
    }
}