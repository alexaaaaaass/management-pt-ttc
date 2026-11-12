<?php

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\MasterSupplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Services\PurchaseOrder\PurchaseOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index(Request $request, PurchaseOrderService $poService): JsonResponse
    {
        $query = PurchaseOrder::with(['purchaseRequest.departemen', 'supplier', 'items.item']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('no_po', 'like', "%{$search}%")
                  ->orWhereHas('purchaseRequest', fn ($pq) => $pq->where('nomor_pr', 'like', "%{$search}%"))
                  ->orWhereHas('supplier', fn ($sq) => $sq->where('nama_supplier', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        $perPage = min(100, max(5, $request->integer('per_page', 15)));
        $data = $query->latest('id')->paginate($perPage);

        $rows = collect($data->items())->map(function (PurchaseOrder $po) use ($poService) {
            return array_merge($po->toArray(), [
                'subtotal_items' => (float) $po->items->sum('total'),
                'can_modify'     => $poService->canModify($po),
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
        $prs = PurchaseRequest::with(['departemen', 'items.item.satuan'])
            ->where('status', 'otorisasi')
            ->latest('id')
            ->get();

        $suppliers = MasterSupplier::select('id', 'kode_supplier', 'nama_supplier', 'jenis_supplier')
            ->orderBy('nama_supplier')
            ->get();

        return response()->json([
            'purchase_requests' => $prs,
            'suppliers'         => $suppliers,
        ]);
    }

    public function store(Request $request, PurchaseOrderService $poService): JsonResponse
    {
        $validated = $request->validate([
            'purchase_request_id' => ['required', 'exists:purchase_requests,id'],
            'supplier_id'         => ['required', 'exists:master_suppliers,id'],
            'po_date'             => ['required', 'date'],
            'eta'                 => ['nullable', 'date'],
            'po_type'             => ['required', 'in:reguler,subcount'],
            'currency'            => ['nullable', 'string', 'max:10'],
            'ppn'                 => ['nullable', 'numeric', 'min:0'],
            'ongkir'              => ['nullable', 'numeric', 'min:0'],
            'dp'                  => ['nullable', 'numeric', 'min:0'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.item_id'     => ['required', 'exists:master_items,id'],
            'items.*.qty_pr'      => ['nullable', 'numeric', 'min:0'],
            'items.*.qty_po'      => ['required', 'numeric', 'min:0.01'],
            'items.*.price'       => ['required', 'numeric', 'min:0'],
            'items.*.discount'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.satuan'      => ['nullable', 'string', 'max:50'],
            'items.*.eta'         => ['nullable', 'date'],
            'items.*.catatan'     => ['nullable', 'string'],
        ]);

        $po = PurchaseOrder::create([
            'purchase_request_id' => $validated['purchase_request_id'],
            'supplier_id'         => $validated['supplier_id'],
            'po_date'             => $validated['po_date'],
            'eta'                 => $validated['eta'] ?? null,
            'po_type'             => $validated['po_type'],
            'currency'            => $validated['currency'] ?? 'IDR',
            'ppn'                 => (float) ($validated['ppn'] ?? 11),
            'ongkir'              => (float) ($validated['ongkir'] ?? 0),
            'dp'                  => (float) ($validated['dp'] ?? 0),
            'status'              => 'Open',
        ]);

        $itemsPayload = collect($validated['items'])->map(function ($it) {
            $qty = (float) $it['qty_po'];
            $price = (float) $it['price'];
            $disc = (float) ($it['discount'] ?? 0);
            $total = ($qty * $price) * (1 - ($disc / 100));

            return [
                'item_id'      => $it['item_id'],
                'qty_pr'       => $it['qty_pr'] ?? $qty,
                'qty_po'       => $qty,
                'qty_konversi' => 1,
                'price'        => $price,
                'discount'     => $disc,
                'total'        => $total,
                'satuan'       => $it['satuan'] ?? null,
                'eta'          => $it['eta'] ?? null,
                'catatan'      => $it['catatan'] ?? null,
            ];
        })->toArray();

        $poService->create($po, $itemsPayload);

        return response()->json([
            'message' => "Purchase Order {$po->no_po} berhasil diterbitkan.",
            'data'    => $po->fresh(['purchaseRequest', 'supplier', 'items.item']),
        ], 201);
    }

    public function destroy(PurchaseOrder $purchaseOrder, PurchaseOrderService $poService): JsonResponse
    {
        if (! $poService->canModify($purchaseOrder)) {
            return response()->json([
                'message' => 'Tidak dapat dihapus karena PO sudah memiliki Penerimaan Barang atau Faktur terkait.',
            ], 422);
        }

        $purchaseOrder->delete();

        return response()->json([
            'message' => 'Purchase Order berhasil dihapus.',
        ]);
    }
}
