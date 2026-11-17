<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Controller;
use App\Models\CategoryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CategoryItem::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_item_category', 'like', "%{$search}%")
                  ->orWhere('nama_category', 'like', "%{$search}%");
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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode_item_category' => ['required', 'string', 'max:50', 'unique:category_items,kode_item_category'],
            'nama_category'      => ['required', 'string', 'max:255'],
        ]);

        $category = CategoryItem::create($validated);

        return response()->json([
            'message' => 'Category Item berhasil ditambahkan.',
            'data'    => $category,
        ], 201);
    }

    public function update(Request $request, CategoryItem $category): JsonResponse
    {
        $validated = $request->validate([
            'kode_item_category' => ['required', 'string', 'max:50', 'unique:category_items,kode_item_category,' . $category->id],
            'nama_category'      => ['required', 'string', 'max:255'],
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Category Item berhasil diperbarui.',
            'data'    => $category,
        ]);
    }

    public function destroy(CategoryItem $category): JsonResponse
    {
        if ($category->typeItems()->exists() || $category->items()->exists()) {
            return response()->json([
                'message' => 'Category Item tidak dapat dihapus karena masih memiliki Type Item atau Master Item terkait.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category Item berhasil dihapus.',
        ]);
    }
}

