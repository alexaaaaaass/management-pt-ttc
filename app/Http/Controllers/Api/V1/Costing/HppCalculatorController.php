<?php

namespace App\Http\Controllers\Api\V1\Costing;

use App\Http\Controllers\Controller;
use App\Services\Costing\FlexoHppCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HppCalculatorController extends Controller
{
    /**
     * Hitung HPP & Kebutuhan Kertas Flexo Zonten secara instan via API.
     */
    public function calculate(Request $request, FlexoHppCalculatorService $calculator): JsonResponse
    {
        $validated = $request->validate([
            'target_order'                   => ['required', 'numeric', 'min:1'],
            'panjang_etiket_jadi'            => ['required', 'numeric', 'min:1'],
            'lebar_etiket_jadi'              => ['required', 'numeric', 'min:1'],
            'gramatur_kertas'                => ['nullable', 'numeric', 'min:1'],
            'harga_bahan_per_kg'             => ['nullable', 'numeric', 'min:0'],
            'jumlah_etiket_sepanjang_repeat' => ['nullable', 'integer', 'min:1'],
            'jumlah_etiket_sepanjang_lebar'  => ['nullable', 'integer', 'min:1'],
            'jumlah_up_keliling'             => ['nullable', 'integer', 'min:1'],
            'jumlah_baris_lebar'             => ['nullable', 'integer', 'min:1'],
            'lebihan_cetak_panjang'          => ['nullable', 'numeric', 'min:0'],
            'lebihan_cetak_lebar'            => ['nullable', 'numeric', 'min:0'],
            'lebihan_lebar_bahan'            => ['nullable', 'numeric', 'min:0'],
            'repeat_cylinder'                => ['nullable', 'numeric', 'min:1'],
            'waste_setting'                  => ['nullable', 'numeric', 'min:0'],
            'waste_tambahan_persen'          => ['nullable', 'numeric', 'min:0'],
            'toleransi_waste_persen'         => ['nullable', 'numeric', 'min:0'],
            'kecepatan_cetak'                => ['nullable', 'numeric', 'min:1'],
            'waktu_setup'                    => ['nullable', 'numeric', 'min:0'],
            'tarif_mesin_per_jam'            => ['nullable', 'numeric', 'min:0'],
            'biaya_tinta_total'              => ['nullable', 'numeric', 'min:0'],
            'biaya_plate'                    => ['nullable', 'numeric', 'min:0'],
            'biaya_tenaga_kerja'             => ['nullable', 'numeric', 'min:0'],
            'biaya_overhead'                 => ['nullable', 'numeric', 'min:0'],
            'margin_persen'                  => ['nullable', 'numeric', 'min:0'],
            'margin_keuntungan_persen'       => ['nullable', 'numeric', 'min:0'],
        ]);

        // Map any legacy aliases to FlexoHppCalculatorService canonical keys
        $payload = $validated;
        $payload['jumlah_etiket_sepanjang_repeat'] = $validated['jumlah_etiket_sepanjang_repeat'] ?? $validated['jumlah_up_keliling'] ?? 2;
        $payload['jumlah_etiket_sepanjang_lebar']  = $validated['jumlah_etiket_sepanjang_lebar'] ?? $validated['jumlah_baris_lebar'] ?? 2;
        $payload['waste_tambahan_persen']          = $validated['waste_tambahan_persen'] ?? $validated['toleransi_waste_persen'] ?? 10;
        $payload['margin_persen']                  = $validated['margin_persen'] ?? $validated['margin_keuntungan_persen'] ?? 20;

        $targetOrder = (float) $validated['target_order'];
        $result = $calculator->calculate($payload, $targetOrder);

        return response()->json([
            'data' => $result,
        ]);
    }
}
