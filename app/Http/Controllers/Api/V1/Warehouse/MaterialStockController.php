<?php

namespace App\Http\Controllers\Api\V1\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\MaterialMovement;
use App\Models\MaterialStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaterialStockController extends Controller
{
    /**
     * Daftar Stok Material (On Hand, Allocation, Outstanding) dengan Search & Pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = MaterialStock::query()
            ->with(['item.satuan', 'item.kategori', 'item.typeItem']);

        if ($search = trim((string) $request->input('search'))) {
            $query->whereHas('item', function ($q) use ($search) {
                $q->where('nama_master_item', 'like', "%{$search}%")
                  ->orWhere('kode_master_item', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('low_stock_only')) {
            $query->whereHas('item', function ($q) {
                $q->whereColumn('material_stocks.on_hand', '<', 'master_items.min_stock');
            });
        }

        $perPage = min(100, max(5, $request->integer('per_page', 15)));

        return response()->json($query->paginate($perPage));
    }

    /**
     * Riwayat pergerakan (Track) untuk satu Material Stock.
     */
    public function track(MaterialStock $materialStock): JsonResponse
    {
        $materialStock->loadMissing(['item.satuan']);

        $movements = MaterialMovement::with('user')
            ->where('item_id', $materialStock->item_id)
            ->latest('id')
            ->limit(25)
            ->get();

        return response()->json([
            'stock'     => $materialStock,
            'movements' => $movements,
        ]);
    }

    /**
     * Daftar Riwayat Mutasi Material Global (Material History).
     */
    public function movements(Request $request): JsonResponse
    {
        $query = MaterialMovement::with(['item.satuan', 'user']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('remarks', 'like', "%{$search}%")
                  ->orWhereHas('item', fn ($iq) => $iq->where('nama_master_item', 'like', "%{$search}%")->orWhere('kode_master_item', 'like', "%{$search}%"));
            });
        }

        if ($type = $request->input('movement_type')) {
            if ($type !== 'all') {
                $query->where('movement_type', $type);
            }
        }

        $perPage = min(100, max(5, $request->integer('per_page', 15)));

        return response()->json($query->latest('id')->paginate($perPage));
    }
}
