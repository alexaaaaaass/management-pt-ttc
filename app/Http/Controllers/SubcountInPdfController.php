<?php

namespace App\Http\Controllers;

use App\Models\SubcountIn;
use Barryvdh\DomPDF\Facade\Pdf;

class SubcountInPdfController extends Controller
{
 public function preview(SubcountIn $subcountIn)
{
    $subcountIn->load(
        'items.subcountOut',
        'items.itemHasil'
    );

    $pdf = Pdf::loadView(
        'pdf.subcount-in',
        [
            'subcountIn' => $subcountIn
        ]
    );

    $filename = str_replace(
        '/',
        '-',
        $subcountIn->no_subcount
    );

    return $pdf->stream(
        $filename.'.pdf'
    );
}

    public function download(SubcountIn $subcountIn)
{
    $subcountIn->load(
        'items.subcountOut',
        'items.itemHasil'
    );

    $pdf = Pdf::loadView(
        'pdf.subcount-in',
        [
            'subcountIn' => $subcountIn
        ]
    );

    $filename = str_replace(
        '/',
        '-',
        $subcountIn->no_subcount
    );

    return $pdf->download(
        $filename.'.pdf'
    );
}
}