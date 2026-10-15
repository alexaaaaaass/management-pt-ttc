<?php

namespace App\Http\Controllers;

use App\Models\SuratJalan;
use Barryvdh\DomPDF\Facade\Pdf;

class SuratJalanPdfController extends Controller
{
    protected function getSafeFilename(SuratJalan $suratJalan): string
    {
        $sanitized = str_replace(['/', '\\'], '-', $suratJalan->kode_surat_jalan ?: 'SJ-' . $suratJalan->id);
        return 'SuratJalan-' . $sanitized . '.pdf';
    }

    protected function loadRelations(SuratJalan $suratJalan): SuratJalan
    {
        return $suratJalan->loadMissing([
            'salesOrder.customer',
            'salesOrder.itemable',
            'salesOrder.items.material',
            'spk.packagings',
            'items.material.satuan',
            'invoice',
        ]);
    }

    public function preview(SuratJalan $suratJalan)
    {
        $suratJalan = $this->loadRelations($suratJalan);

        $pdf = Pdf::loadView(
            'pdf.surat-jalan',
            compact('suratJalan')
        );

        return $pdf->stream($this->getSafeFilename($suratJalan));
    }

    public function download(SuratJalan $suratJalan)
    {
        $suratJalan = $this->loadRelations($suratJalan);

        $pdf = Pdf::loadView(
            'pdf.surat-jalan',
            compact('suratJalan')
        );

        return $pdf->download($this->getSafeFilename($suratJalan));
    }
}
