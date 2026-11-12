<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Controller;
use App\Models\TypeItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TypeItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TypeItem::with('category');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_type_item', 'like', "%{$search}%")
                  ->orWhere('nama_type_item', 'like', "%{$search}%")
                  ->orWhereHas('category', fn ($cq) => $cq->where('nama_category', 'like', "%{$search}%"));
            });
        }

        if ($categoryId = $request->input('category_item_id')) {
            $query->where('category_item_id', $categoryId);
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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode_type_item'   => ['required', 'string', 'max:50', 'unique:type_items,kode_type_item'],
            'nama_type_item'   => ['required', 'string', 'max:255'],
            'category_item_id' => ['required', 'exists:category_items,id'],
        ]);

        $type = TypeItem::create($validated);
        $type->load('category');

        return response()->json([
            'message' => 'Type Item berhasil ditambahkan.',
            'data'    => $type,
        ], 201);
    }

    public function update(Request $request, TypeItem $type): JsonResponse
    {
        $validated = $request->validate([
            'kode_type_item'   => ['required', 'string', 'max:50', 'unique:type_items,kode_type_item,' . $type->id],
            'nama_type_item'   => ['required', 'string', 'max:255'],
            'category_item_id' => ['required', 'exists:category_items,id'],
        ]);

        $type->update($validated);
        $type->load('category');

        return response()->json([
            'message' => 'Type Item berhasil diperbarui.',
            'data'    => $type,
        ]);
    }

    public function destroy(TypeItem $type): JsonResponse
    {
        if ($type->items()->exists()) {
            return response()->json([
                'message' => 'Type Item tidak dapat dihapus karena masih digunakan di Master Item.',
            ], 422);
        }

        $type->delete();

        return response()->json([
            'message' => 'Type Item berhasil dihapus.',
        ]);
    }
}
