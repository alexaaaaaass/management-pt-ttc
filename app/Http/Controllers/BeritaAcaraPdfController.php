<?php

namespace App\Http\Controllers;

use App\Models\BeritaAcara;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class BeritaAcaraPdfController extends Controller
{
    public function preview(BeritaAcara $beritaAcara)
    {
        $beritaAcara->load([
            'customer',
            'suratJalan.salesOrder.customer',
            'suratJalan.items.material',
            'invoice',
            'items',
        ]);

        $items = $beritaAcara->items->map(fn ($item) => [
            'item_product' => $item->item_product ?: $beritaAcara->item_product,
            'qty_kedatangan' => $item->qty_kedatangan,
            'qty_penerimaan' => $item->qty_penerimaan,
            'qty_penolakan' => $item->qty_penolakan,
            'catatan_penerimaan' => $item->catatan_penerimaan,
            'foto_referensi' => $this->imageDataUri($item->foto_referensi),
            'foto_fisik' => $this->imageDataUri($item->foto_fisik),
        ]);

        if ($items->isEmpty()) {
            $items = collect([[
                'item_product' => $beritaAcara->item_product,
                'qty_kedatangan' => null,
                'qty_penerimaan' => null,
                'qty_penolakan' => null,
                'catatan_penerimaan' => null,
                'foto_referensi' => null,
                'foto_fisik' => null,
            ]]);
        }

        $logoPath = public_path('images/Logo PT Teknografi.png');
        $logo = is_file($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView('pdf.berita-acara', [
            'beritaAcara' => $beritaAcara,
            'items' => $items,
            'logo' => $logo,
            'customer' => $beritaAcara->customer ?? $beritaAcara->suratJalan?->salesOrder?->customer,
            'deliveryDate' => $beritaAcara->suratJalan?->tanggal_surat_jalan,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream($this->filename($beritaAcara));
    }

    private function imageDataUri(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $fullPath = Storage::disk('public')->path($path);
        if (! is_file($fullPath)) {
            return null;
        }

        $mime = mime_content_type($fullPath) ?: 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
    }

    private function filename(BeritaAcara $beritaAcara): string
    {
        $number = str_replace(['/', '\\'], '-', $beritaAcara->no_ba);

        return 'Berita-Acara-' . $number . '.pdf';
    }
}