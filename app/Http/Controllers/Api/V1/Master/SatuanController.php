<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterSatuan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SatuanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MasterSatuan::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_satuan', 'like', "%{$search}%")
                  ->orWhere('nama_satuan', 'like', "%{$search}%");
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
            'kode_satuan' => ['required', 'string', 'max:50', 'unique:master_satuans,kode_satuan'],
            'nama_satuan' => ['required', 'string', 'max:255'],
        ]);

        $satuan = MasterSatuan::create($validated);

        return response()->json([
            'message' => 'Satuan berhasil ditambahkan.',
            'data'    => $satuan,
        ], 201);
    }

    public function update(Request $request, MasterSatuan $satuan): JsonResponse
    {
        $validated = $request->validate([
            'kode_satuan' => ['required', 'string', 'max:50', 'unique:master_satuans,kode_satuan,' . $satuan->id],
            'nama_satuan' => ['required', 'string', 'max:255'],
        ]);

        $satuan->update($validated);

        return response()->json([
            'message' => 'Satuan berhasil diperbarui.',
            'data'    => $satuan,
        ]);
    }

    public function destroy(MasterSatuan $satuan): JsonResponse
    {
        if ($satuan->items()->exists()) {
            return response()->json([
                'message' => 'Satuan tidak dapat dihapus karena digunakan di Master Item.',
            ], 422);
        }

        $satuan->delete();

        return response()->json([
            'message' => 'Satuan berhasil dihapus.',
        ]);
    }
}
