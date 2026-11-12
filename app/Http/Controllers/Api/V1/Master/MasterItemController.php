<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Controller;
use App\Models\CategoryItem;
use App\Models\MasterItem;
use App\Models\MasterSatuan;
use App\Models\TypeItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MasterItem::with(['satuan', 'kategori', 'typeItem', 'stock']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_master_item', 'like', "%{$search}%")
                  ->orWhere('nama_master_item', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $data = $query->latest('id')->paginate($perPage);

        return response()->json([
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
            ],
        ]);
    }

    public function options(): JsonResponse
    {
        return response()->json([
            'satuans'    => MasterSatuan::select('id', 'kode_satuan', 'nama_satuan')->orderBy('nama_satuan')->get(),
            'categories' => CategoryItem::select('id', 'kode_item_category', 'nama_category')->orderBy('nama_category')->get(),
            'types'      => TypeItem::select('id', 'kode_type_item', 'nama_type_item', 'category_item_id')->orderBy('nama_type_item')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode_master_item' => ['required', 'string', 'max:50', 'unique:master_items,kode_master_item'],
            'nama_master_item' => ['required', 'string', 'max:255'],
            'satuan_id'        => ['required', 'exists:master_satuans,id'],
            'category_item_id' => ['required', 'exists:category_items,id'],
            'type_item_id'     => ['required', 'exists:type_items,id'],
            'min_stock'        => ['nullable', 'numeric', 'min:0'],
            'min_order'        => ['nullable', 'numeric', 'min:0'],
            'qty'              => ['nullable', 'numeric', 'min:0'],
            'panjang'          => ['nullable', 'numeric', 'min:0'],
            'lebar'            => ['nullable', 'numeric', 'min:0'],
            'tinggi'           => ['nullable', 'numeric', 'min:0'],
            'berat'            => ['nullable', 'numeric', 'min:0'],
        ]);

        $item = MasterItem::create($validated);
        $item->load(['satuan', 'kategori', 'typeItem']);

        return response()->json([
            'message' => 'Master Item berhasil ditambahkan.',
            'data'    => $item,
        ], 201);
    }

    public function update(Request $request, MasterItem $masterItem): JsonResponse
    {
        $validated = $request->validate([
            'kode_master_item' => ['required', 'string', 'max:50', 'unique:master_items,kode_master_item,' . $masterItem->id],
            'nama_master_item' => ['required', 'string', 'max:255'],
            'satuan_id'        => ['required', 'exists:master_satuans,id'],
            'category_item_id' => ['required', 'exists:category_items,id'],
            'type_item_id'     => ['required', 'exists:type_items,id'],
            'min_stock'        => ['nullable', 'numeric', 'min:0'],
            'min_order'        => ['nullable', 'numeric', 'min:0'],
            'qty'              => ['nullable', 'numeric', 'min:0'],
            'panjang'          => ['nullable', 'numeric', 'min:0'],
            'lebar'            => ['nullable', 'numeric', 'min:0'],
            'tinggi'           => ['nullable', 'numeric', 'min:0'],
            'berat'            => ['nullable', 'numeric', 'min:0'],
        ]);

        $masterItem->update($validated);
        $masterItem->load(['satuan', 'kategori', 'typeItem']);

        return response()->json([
            'message' => 'Master Item berhasil diperbarui.',
            'data'    => $masterItem,
        ]);
    }

    public function destroy(MasterItem $masterItem): JsonResponse
    {
        if ($masterItem->movements()->exists()) {
            return response()->json([
                'message' => 'Master Item tidak dapat dihapus karena sudah memiliki riwayat mutasi stok.',
            ], 422);
        }

        $masterItem->delete();

        return response()->json([
            'message' => 'Master Item berhasil dihapus.',
        ]);
    }
}

