<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\FinishGoodItem;
use App\Models\MasterDepartemen;
use App\Models\MasterItem;
use App\Models\MasterSatuan;
use App\Models\TypeItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinishGoodItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = FinishGoodItem::with(['customer', 'typeItem', 'satuan', 'materials.item', 'materials.departemen']);

        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_material_produk', 'like', "%{$search}%")
                  ->orWhere('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barcode', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($cq) => $cq->where('nama_customer', 'like', "%{$search}%"));
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
            'customers'   => Customer::select('id', 'kode_customer', 'nama_customer')->orderBy('nama_customer')->get(),
            'types'       => TypeItem::select('id', 'kode_type_item', 'nama_type_item')->orderBy('nama_type_item')->get(),
            'satuans'     => MasterSatuan::select('id', 'kode_satuan', 'nama_satuan')->orderBy('nama_satuan')->get(),
            'materials'   => MasterItem::with('satuan:id,nama_satuan')->select('id', 'kode_master_item', 'nama_master_item', 'satuan_id')->orderBy('nama_master_item')->get(),
            'departemens' => MasterDepartemen::select('id', 'nama_departemen')->orderBy('nama_departemen')->get(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $item = FinishGoodItem::withTrashed()
            ->with(['customer', 'typeItem', 'satuan', 'materials.item', 'materials.departemen'])
            ->findOrFail($id);

        return response()->json(['data' => $item]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id'          => ['required', 'exists:customers,id'],
            'type_item_id'         => ['required', 'exists:type_items,id'],
            'satuan_id'            => ['required', 'exists:master_satuans,id'],
            'kode_material_produk' => ['required', 'string', 'max:100', 'unique:finish_good_items,kode_material_produk'],
            'nama_barang'          => ['required', 'string', 'max:255'],
            'kode_barcode'         => ['nullable', 'string', 'max:100'],
            'pc_number'            => ['nullable', 'string', 'max:100'],
            'jenis'                => ['nullable', 'string', 'max:100'],
            'komponen'             => ['nullable', 'string', 'max:100'],
            'isi'                  => ['nullable', 'string', 'max:100'],
            'warna'                => ['nullable', 'string', 'max:100'],
            'finishing'            => ['nullable', 'string', 'max:255'],
            'deskripsi'            => ['nullable', 'string'],
            'spesifikasi_kertas'   => ['nullable', 'string'],
            'gramasi'              => ['nullable', 'string'],
            'ukuran_potong'        => ['nullable', 'string'],
            'ukuran_cetak'         => ['nullable', 'string'],
            'materials'            => ['nullable', 'array'],
            'materials.*.master_item_id' => ['required_with:materials', 'exists:master_items,id'],
            'materials.*.departemen_id'  => ['nullable', 'exists:master_departemens,id'],
            'materials.*.qty'            => ['nullable', 'numeric', 'min:0'],
            'materials.*.waste'          => ['nullable', 'numeric', 'min:0'],
            'materials.*.keterangan'     => ['nullable', 'string'],
        ]);

        $finishGood = DB::transaction(function () use ($validated) {
            $materialsData = $validated['materials'] ?? [];
            unset($validated['materials']);

            $fg = FinishGoodItem::create($validated);

            foreach ($materialsData as $mat) {
                $fg->materials()->create([
                    'master_item_id' => $mat['master_item_id'],
                    'departemen_id'  => $mat['departemen_id'] ?? null,
                    'qty'            => $mat['qty'] ?? 0,
                    'waste'          => $mat['waste'] ?? 0,
                    'keterangan'     => $mat['keterangan'] ?? null,
                ]);
            }

            return $fg->load(['customer', 'typeItem', 'satuan', 'materials.item', 'materials.departemen']);
        });

        return response()->json([
            'message' => 'Finish Good Item & BOM berhasil dibuat.',
            'data'    => $finishGood,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $finishGoodItem = FinishGoodItem::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'customer_id'          => ['required', 'exists:customers,id'],
            'type_item_id'         => ['required', 'exists:type_items,id'],
            'satuan_id'            => ['required', 'exists:master_satuans,id'],
            'kode_material_produk' => ['required', 'string', 'max:100', 'unique:finish_good_items,kode_material_produk,' . $finishGoodItem->id],
            'nama_barang'          => ['required', 'string', 'max:255'],
            'kode_barcode'         => ['nullable', 'string', 'max:100'],
            'pc_number'            => ['nullable', 'string', 'max:100'],
            'jenis'                => ['nullable', 'string', 'max:100'],
            'komponen'             => ['nullable', 'string', 'max:100'],
            'isi'                  => ['nullable', 'string', 'max:100'],
            'warna'                => ['nullable', 'string', 'max:100'],
            'finishing'            => ['nullable', 'string', 'max:255'],
            'deskripsi'            => ['nullable', 'string'],
            'spesifikasi_kertas'   => ['nullable', 'string'],
            'gramasi'              => ['nullable', 'string'],
            'ukuran_potong'        => ['nullable', 'string'],
            'ukuran_cetak'         => ['nullable', 'string'],
            'materials'            => ['nullable', 'array'],
            'materials.*.master_item_id' => ['required_with:materials', 'exists:master_items,id'],
            'materials.*.departemen_id'  => ['nullable', 'exists:master_departemens,id'],
            'materials.*.qty'            => ['nullable', 'numeric', 'min:0'],
            'materials.*.waste'          => ['nullable', 'numeric', 'min:0'],
            'materials.*.keterangan'     => ['nullable', 'string'],
        ]);

        $updated = DB::transaction(function () use ($finishGoodItem, $validated) {
            $materialsData = $validated['materials'] ?? null;
            unset($validated['materials']);

            $finishGoodItem->update($validated);

            if (is_array($materialsData)) {
                $finishGoodItem->materials()->delete();
                foreach ($materialsData as $mat) {
                    $finishGoodItem->materials()->create([
                        'master_item_id' => $mat['master_item_id'],
                        'departemen_id'  => $mat['departemen_id'] ?? null,
                        'qty'            => $mat['qty'] ?? 0,
                        'waste'          => $mat['waste'] ?? 0,
                        'keterangan'     => $mat['keterangan'] ?? null,
                    ]);
                }
            }

            return $finishGoodItem->load(['customer', 'typeItem', 'satuan', 'materials.item', 'materials.departemen']);
        });

        return response()->json([
            'message' => 'Finish Good Item & BOM berhasil diperbarui.',
            'data'    => $updated,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $item = FinishGoodItem::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => 'Finish Good Item berhasil di-Cut Off (diarsipkan).',
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $item = FinishGoodItem::withTrashed()->findOrFail($id);
        $item->restore();

        return response()->json([
            'message' => 'Finish Good Item berhasil di-Restore.',
            'data'    => $item,
        ]);
    }
}
