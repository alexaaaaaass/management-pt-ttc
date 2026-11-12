<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterSupplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MasterSupplier::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_supplier', 'like', "%{$search}%")
                  ->orWhere('nama_supplier', 'like', "%{$search}%")
                  ->orWhere('jenis_supplier', 'like', "%{$search}%");
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
            'kode_supplier'  => ['required', 'string', 'max:50', 'unique:master_suppliers,kode_supplier'],
            'nama_supplier'  => ['required', 'string', 'max:255'],
            'jenis_supplier' => ['nullable', 'string', 'max:255'],
            'alamat'         => ['nullable', 'string'],
            'keterangan'     => ['nullable', 'string'],
        ]);

        $supplier = MasterSupplier::create($validated);

        return response()->json([
            'message' => 'Supplier berhasil ditambahkan.',
            'data'    => $supplier,
        ], 201);
    }

    public function update(Request $request, MasterSupplier $supplier): JsonResponse
    {
        $validated = $request->validate([
            'kode_supplier'  => ['required', 'string', 'max:50', 'unique:master_suppliers,kode_supplier,' . $supplier->id],
            'nama_supplier'  => ['required', 'string', 'max:255'],
            'jenis_supplier' => ['nullable', 'string', 'max:255'],
            'alamat'         => ['nullable', 'string'],
            'keterangan'     => ['nullable', 'string'],
        ]);

        $supplier->update($validated);

        return response()->json([
            'message' => 'Supplier berhasil diperbarui.',
            'data'    => $supplier,
        ]);
    }

    public function destroy(MasterSupplier $supplier): JsonResponse
    {
        if ($supplier->purchaseOrders()->exists()) {
            return response()->json([
                'message' => 'Supplier tidak dapat dihapus karena masih memiliki Purchase Order terkait.',
            ], 422);
        }

        $supplier->delete();

        return response()->json([
            'message' => 'Supplier berhasil dihapus.',
        ]);
    }
}
