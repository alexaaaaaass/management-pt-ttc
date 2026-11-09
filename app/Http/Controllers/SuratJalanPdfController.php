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
            'salesOrder.spk',
            'spk.packagings',
            'items.material.satuan',
            'invoice.customer',
        ]);
    }

    protected function preparePdf(SuratJalan $suratJalan)
    {
        $suratJalan = $this->loadRelations($suratJalan);

        $logoPath = public_path('images/Logo PT Teknografi.png');
        if (!file_exists($logoPath)) {
            $logoPath = public_path('images/logo.png');
        }

        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }

        $pdf = Pdf::loadView('pdf.surat-jalan', [
            'suratJalan' => $suratJalan,
            'logoBase64' => $logoBase64,
        ]);

        $pdf->setPaper('A4', 'landscape');

        return $pdf;
    }

    public function preview(SuratJalan $suratJalan)
    {
        $pdf = $this->preparePdf($suratJalan);
        return $pdf->stream($this->getSafeFilename($suratJalan));
    }

    public function download(SuratJalan $suratJalan)
    {
        $pdf = $this->preparePdf($suratJalan);
        return $pdf->download($this->getSafeFilename($suratJalan));
    }
}
