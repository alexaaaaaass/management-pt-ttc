<?php

namespace App\Http\Controllers\Api\V1\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanBarang;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrder\PenerimaanBarangService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PenerimaanBarangController extends Controller
{
    public function index(Request $request, PenerimaanBarangService $pbService): JsonResponse
    {
        $query = PenerimaanBarang::with(['purchaseOrder.supplier', 'items.purchaseOrderItem.item']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('no_penerimaan', 'like', "%{$search}%")
                  ->orWhere('no_surat_jalan', 'like', "%{$search}%")
                  ->orWhereHas('purchaseOrder', fn ($pq) => $pq->where('no_po', 'like', "%{$search}%"));
            });
        }

        $perPage = min(100, max(5, $request->integer('per_page', 15)));
        $data = $query->latest('id')->paginate($perPage);

        $rows = collect($data->items())->map(function (PenerimaanBarang $pb) use ($pbService) {
            return array_merge($pb->toArray(), [
                'can_modify' => $pbService->canModify($pb),
            ]);
        });

        return response()->json([
            'data' => $rows,
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
        $openPos = PurchaseOrder::with(['supplier', 'items.item.satuan'])
            ->whereIn('status', ['Open', 'Partial'])
            ->latest('id')
            ->get()
            ->map(function (PurchaseOrder $po) {
                return [
                    'id'            => $po->id,
                    'no_po'         => $po->no_po,
                    'supplier_name' => $po->supplier?->nama_supplier,
                    'status'        => $po->status,
                    'items'         => $po->items->map(function ($it) {
                        $sisa = max(0, (float) $it->qty_po - (float) $it->qty_received);
                        return [
                            'purchase_order_item_id' => $it->id,
                            'item_id'                => $it->item_id,
                            'nama_item'              => $it->item?->nama_master_item,
                            'kode_item'              => $it->item?->kode_master_item,
                            'satuan'                 => $it->satuan ?? $it->item?->satuan?->nama_satuan,
                            'qty_po'                 => (float) $it->qty_po,
                            'qty_received'           => (float) $it->qty_received,
                            'sisa_terima'            => $sisa,
                        ];
                    })->filter(fn ($it) => $it['sisa_terima'] > 0)->values(),
                ];
            });

        return response()->json([
            'purchase_orders' => $openPos,
        ]);
    }

    public function store(Request $request, PenerimaanBarangService $pbService): JsonResponse
    {
        $validated = $request->validate([
            'purchase_order_id'               => ['required', 'exists:purchase_orders,id'],
            'tanggal_terima'                  => ['required', 'date'],
            'no_surat_jalan'                  => ['nullable', 'string', 'max:100'],
            'nama_pengirim'                   => ['nullable', 'string', 'max:255'],
            'nopol_kendaraan'                 => ['nullable', 'string', 'max:50'],
            'catatan_pengiriman'              => ['nullable', 'string'],
            'items'                           => ['required', 'array', 'min:1'],
            'items.*.purchase_order_item_id'  => ['required', 'integer'],
            'items.*.qty_terima'              => ['required', 'numeric', 'min:0'],
        ]);

        $pbData = [
            'purchase_order_id'  => $validated['purchase_order_id'],
            'tanggal_terima'     => $validated['tanggal_terima'],
            'no_surat_jalan'     => $validated['no_surat_jalan'] ?? '-',
            'nama_pengirim'      => $validated['nama_pengirim'] ?? '-',
            'nopol_kendaraan'    => $validated['nopol_kendaraan'] ?? '-',
            'catatan_pengiriman' => $validated['catatan_pengiriman'] ?? null,
        ];

        $pb = $pbService->createPenerimaanBarang($pbData, $validated['items']);

        return response()->json([
            'message' => "Penerimaan Barang {$pb->no_penerimaan} berhasil diproses dan stok gudang telah diperbarui.",
            'data'    => $pb->load(['purchaseOrder.supplier', 'items.purchaseOrderItem.item']),
        ], 201);
    }

    public function destroy(PenerimaanBarang $penerimaanBarang, PenerimaanBarangService $pbService): JsonResponse
    {
        if (! $pbService->canModify($penerimaanBarang)) {
            return response()->json([
                'message' => 'Tidak dapat dihapus karena Penerimaan Barang sudah memiliki Retur Eksternal terkait.',
            ], 422);
        }

        $penerimaanBarang->delete();

        return response()->json([
            'message' => 'Penerimaan Barang dihapus dan stok material berhasil di-rollback.',
        ]);
    }
}
