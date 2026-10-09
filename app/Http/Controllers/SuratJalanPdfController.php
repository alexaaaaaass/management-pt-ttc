<?php

namespace App\Http\Controllers;

use App\Models\SuratJalan;
use Barryvdh\DomPDF\Facade\Pdf;

class SuratJalanPdfController extends Controller
{
    protected function preparePdf(SuratJalan $suratJalan)
    {
        $suratJalan->load([
            'salesOrder.customer',
            'salesOrder.itemable',
            'spk',
            'invoice',
        ]);

        $logoPath = public_path('images/Logo PT Teknografi.png');
        $logoBase64 = '';

        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/svg+xml;base64,' . base64_encode($logoData);
        }

        $pdf = Pdf::loadView('pdf.surat-jalan', [
            'suratJalan' => $suratJalan,
            'logoBase64' => $logoBase64,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    public function preview(SuratJalan $suratJalan)
    {
        $pdf = $this->preparePdf($suratJalan);
        $filename = 'Surat_Jalan_' . str_replace(['/', '\\'], '_', $suratJalan->kode_surat_jalan) . '.pdf';

        return $pdf->stream($filename);
    }

    public function download(SuratJalan $suratJalan)
    {
        $pdf = $this->preparePdf($suratJalan);
        $filename = 'Surat_Jalan_' . str_replace(['/', '\\'], '_', $suratJalan->kode_surat_jalan) . '.pdf';

        return $pdf->download($filename);
    }
}
