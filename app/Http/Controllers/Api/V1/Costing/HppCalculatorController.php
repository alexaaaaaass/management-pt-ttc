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
            'target_order'            => ['required', 'numeric', 'min:1'],
            'panjang_etiket_jadi'     => ['required', 'numeric', 'min:1'],
            'lebar_etiket_jadi'       => ['required', 'numeric', 'min:1'],
            'gramatur_kertas'         => ['nullable', 'numeric', 'min:1'],
            'harga_bahan_per_kg'      => ['nullable', 'numeric', 'min:0'],
            'jumlah_up_keliling'      => ['nullable', 'integer', 'min:1'],
            'jumlah_baris_lebar'      => ['nullable', 'integer', 'min:1'],
            'gap_antar_etiket_lebar'  => ['nullable', 'numeric', 'min:0'],
            'margin_sisi_kiri_kanan'  => ['nullable', 'numeric', 'min:0'],
            'toleransi_waste_persen'  => ['nullable', 'numeric', 'min:0'],
            'margin_keuntungan_persen'=> ['nullable', 'numeric', 'min:0'],
        ]);

        $targetOrder = (float) $validated['target_order'];
        $result = $calculator->calculate($validated, $targetOrder);

        return response()->json([
            'data' => $result,
        ]);
    }
}

