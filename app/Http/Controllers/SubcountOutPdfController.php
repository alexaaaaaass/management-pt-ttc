<?php

namespace App\Http\Controllers;

use App\Models\SubcountOut;
use Barryvdh\DomPDF\Facade\Pdf;

class SubcountOutPdfController extends Controller
{
    public function preview(SubcountOut $subcountOut)
    {
        $subcountOut->load(
            'supplier',
            'items.spk.salesOrder.itemable',
            'items.item',
            'items.satuan'
        );

        $pdf = Pdf::loadView(
            'pdf.subcount-out',
            [
                'subcountOut' => $subcountOut
            ]
        );

        $filename = str_replace(
            '/',
            '-',
            $subcountOut->surat_jalan_subcount
        );

        return $pdf->stream(
            $filename . '.pdf'
        );
    }

    public function download(SubcountOut $subcountOut)
    {
        $subcountOut->load(
            'supplier',
            'items.spk.salesOrder.itemable',
            'items.item',
            'items.satuan'
        );

        $pdf = Pdf::loadView(
            'pdf.subcount-out',
            [
                'subcountOut' => $subcountOut
            ]
        );

        $filename = str_replace(
            '/',
            '-',
            $subcountOut->surat_jalan_subcount
        );

        return $pdf->download(
            $filename . '.pdf'
        );
    }
}