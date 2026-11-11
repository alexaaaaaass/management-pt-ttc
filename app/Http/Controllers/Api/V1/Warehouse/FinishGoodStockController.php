<?php

namespace App\Http\Controllers\Api\V1\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\FinishGoodMovement;
use App\Models\FinishGoodStock;
use App\Services\Inventory\FinishGoodStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinishGoodStockController extends Controller
{
    /**
     * Daftar Stok Barang Jadi (Finish Good Stocks) dengan Search & Pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = FinishGoodStock::query()
            ->with(['finishGoodItem.customer', 'finishGoodItem.satuan']);

        if ($search = trim((string) $request->input('search'))) {
            $query->whereHas('finishGoodItem', function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_material_produk', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($cq) => $cq->where('nama_customer', 'like', "%{$search}%"));
            });
        }

        if ($customerId = $request->integer('customer_id')) {
            $query->whereHas('finishGoodItem', fn ($q) => $q->where('customer_id', $customerId));
        }

        $perPage = min(100, max(5, $request->integer('per_page', 15)));

        return response()->json($query->paginate($perPage));
    }

    /**
     * Detail Tracking & Ringkasan Mutasi untuk 1 Barang Jadi.
     */
    public function track(FinishGoodStock $finishGoodStock): JsonResponse
    {
        $finishGoodStock->loadMissing(['finishGoodItem.customer', 'finishGoodItem.satuan']);

        $movements = FinishGoodMovement::with(['spk', 'user'])
            ->where('finish_good_item_id', $finishGoodStock->finish_good_item_id)
            ->latest('id')
            ->limit(20)
            ->get();

        $totalPackagingIn = (float) FinishGoodMovement::where('finish_good_item_id', $finishGoodStock->finish_good_item_id)
            ->where('movement_type', 'IN')
            ->where('source', 'PACKAGING')
            ->sum('qty');

        $totalSubcountIn = (float) FinishGoodMovement::where('finish_good_item_id', $finishGoodStock->finish_good_item_id)
            ->where('movement_type', 'IN')
            ->where('source', 'SUBCOUNT_IN')
            ->sum('qty');

        $totalDispatched = (float) FinishGoodMovement::where('finish_good_item_id', $finishGoodStock->finish_good_item_id)
            ->where('movement_type', 'OUT')
            ->where('source', 'SURAT_JALAN')
            ->sum('qty');

        return response()->json([
            'stock'   => $finishGoodStock,
            'summary' => [
                'total_packaging_in' => $totalPackagingIn,
                'total_subcount_in'  => $totalSubcountIn,
                'total_dispatched'   => $totalDispatched,
            ],
            'movements' => $movements,
        ]);
    }

    /**
     * Penyesuaian Stok Barang Jadi (Stock Opname) melalui FinishGoodStockService.
     */
    public function adjust(Request $request, FinishGoodStock $finishGoodStock, FinishGoodStockService $service): JsonResponse
    {
        $validated = $request->validate([
            'new_on_hand' => ['required', 'numeric', 'min:0'],
            'new_blocked' => ['required', 'numeric', 'min:0'],
            'remarks'     => ['required', 'string', 'max:500'],
        ]);

        $updated = $service->adjustStock(
            (int) $finishGoodStock->finish_good_item_id,
            (float) $validated['new_on_hand'],
            (float) $validated['new_blocked'],
            $validated['remarks']
        );

        return response()->json([
            'message' => 'Stok Barang Jadi berhasil disesuaikan.',
            'data'    => $updated->load(['finishGoodItem.customer', 'finishGoodItem.satuan']),
        ]);
    }

    /**
     * Riwayat Kartu Stok Barang Jadi (Finish Good Ledgers).
     */
    public function ledgers(Request $request): JsonResponse
    {
        $query = FinishGoodMovement::query()
            ->with(['finishGoodItem', 'spk', 'user'])
            ->latest('id');

        if ($movementType = $request->input('movement_type')) {
            $query->where('movement_type', $movementType);
        }

        if ($source = $request->input('source')) {
            $query->where('source', $source);
        }

        if ($fgItemId = $request->integer('finish_good_item_id')) {
            $query->where('finish_good_item_id', $fgItemId);
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->whereHas('finishGoodItem', function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_material_produk', 'like', "%{$search}%");
            });
        }

        $perPage = min(100, max(5, $request->integer('per_page', 15)));

        return response()->json($query->paginate($perPage));
    }
}

