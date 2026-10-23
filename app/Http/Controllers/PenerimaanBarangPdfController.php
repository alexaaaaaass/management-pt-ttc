<?php

namespace App\Http\Controllers;

use App\Models\PenerimaanBarang;
use Barryvdh\DomPDF\Facade\Pdf;

class PenerimaanBarangPdfController extends Controller
{
    protected function loadRelations(PenerimaanBarang $penerimaanBarang): PenerimaanBarang
    {
        return $penerimaanBarang->loadMissing([
            'purchaseOrder.supplier',
            'items.purchaseOrderItem.item.satuan',
        ]);
    }

    protected function getSafeFilename(PenerimaanBarang $penerimaanBarang): string
    {
        $sanitized = str_replace(['/', '\\'], '-', $penerimaanBarang->no_penerimaan ?: 'PB-' . $penerimaanBarang->id);
        return 'penerimaan-barang-' . $sanitized . '.pdf';
    }

    public function preview(PenerimaanBarang $penerimaanBarang)
    {
        $pb = $this->loadRelations($penerimaanBarang);

        $pdf = Pdf::loadView('pdf.penerimaan-barang', compact('pb'));

        return $pdf->stream($this->getSafeFilename($pb));
    }

    public function download(PenerimaanBarang $penerimaanBarang)
    {
        $pb = $this->loadRelations($penerimaanBarang);

        $pdf = Pdf::loadView('pdf.penerimaan-barang', compact('pb'));

        return $pdf->download($this->getSafeFilename($pb));
    }
}
