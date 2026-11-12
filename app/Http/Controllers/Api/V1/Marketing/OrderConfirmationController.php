<?php

namespace App\Http\Controllers\Api\V1\Marketing;

use App\Http\Controllers\Controller;
use App\Models\OrderConfirmation;
use App\Models\OrderConfirmationItem;
use App\Services\Marketing\MarketingWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderConfirmationController extends Controller
{
    public function index(Request $request, MarketingWorkflowService $workflow): JsonResponse
    {
        $query = OrderConfirmation::with(['customer', 'items.finishGoodItem', 'approvalForm', 'salesOrders']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('doc_no', 'like', "%{$search}%")
                  ->orWhere('order_id', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($cq) => $cq->where('nama_customer', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        $perPage = (int) $request->input('per_page', 15);
        $data = $query->latest('id')->paginate($perPage);

        $rows = collect($data->items())->map(function (OrderConfirmation $oc) use ($workflow) {
            return array_merge($oc->toArray(), [
                'so_conversion_status' => $oc->so_conversion_status,
                'progress'             => $workflow->getOrderConfirmationProgress($oc),
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

    public function show(OrderConfirmation $orderConfirmation, MarketingWorkflowService $workflow): JsonResponse
    {
        $orderConfirmation->load(['customer', 'items.finishGoodItem', 'approvalForm', 'salesOrders']);

        return response()->json([
            'data' => array_merge($orderConfirmation->toArray(), [
                'so_conversion_status' => $orderConfirmation->so_conversion_status,
                'progress'             => $workflow->getOrderConfirmationProgress($orderConfirmation),
            ]),
        ]);
    }

    public function update(Request $request, OrderConfirmation $orderConfirmation): JsonResponse
    {
        $validated = $request->validate([
            'tanggal'                      => ['required', 'date'],
            'lead_time'                    => ['nullable', 'string', 'max:100'],
            'diskon'                       => ['nullable', 'numeric', 'min:0'],
            'terms_conditions'             => ['nullable', 'string'],
            'catatan'                      => ['nullable', 'string'],
            'status'                       => ['required', 'in:draft,sent,deal,partial_so,converted_to_so,cancelled'],
            'disetujui_customer'           => ['nullable', 'string', 'max:255'],
            'diketahui_ttc'                => ['nullable', 'string', 'max:255'],
            'items'                        => ['required', 'array', 'min:1'],
            'items.*.id'                   => ['nullable', 'integer'],
            'items.*.item_name'            => ['required', 'string', 'max:255'],
            'items.*.qty'                  => ['required', 'numeric', 'min:1'],
            'items.*.harga'                => ['required', 'numeric', 'min:0'],
            'items.*.satuan'               => ['nullable', 'string', 'max:50'],
            'items.*.hpp_per_pcs'          => ['nullable', 'numeric', 'min:0'],
            'items.*.harga_jual_indikatif' => ['nullable', 'numeric', 'min:0'],
            'items.*.total_biaya_order'    => ['nullable', 'numeric', 'min:0'],
            'items.*.biaya_bahan'          => ['nullable', 'numeric', 'min:0'],
            'items.*.biaya_proses'         => ['nullable', 'numeric', 'min:0'],
            'items.*.kebutuhan_kertas_kg'  => ['nullable', 'numeric', 'min:0'],
            'items.*.kebutuhan_kertas_meter'=> ['nullable', 'numeric', 'min:0'],
            'items.*.kalkulasi_hpp_data'   => ['nullable', 'array'],
        ]);

        $updated = DB::transaction(function () use ($orderConfirmation, $validated) {
            $orderConfirmation->update([
                'tanggal'            => $validated['tanggal'],
                'lead_time'          => $validated['lead_time'] ?? $orderConfirmation->lead_time,
                'diskon'             => (float) ($validated['diskon'] ?? 0),
                'terms_conditions'   => $validated['terms_conditions'] ?? $orderConfirmation->terms_conditions,
                'catatan'            => $validated['catatan'] ?? null,
                'status'             => $validated['status'],
                'disetujui_customer' => $validated['disetujui_customer'] ?? null,
                'diketahui_ttc'      => $validated['diketahui_ttc'] ?? null,
            ]);

            foreach ($validated['items'] as $row) {
                if (! empty($row['id'])) {
                    $item = OrderConfirmationItem::where('order_confirmation_id', $orderConfirmation->id)
                        ->find($row['id']);
                    if ($item) {
                        $item->update([
                            'item_name'              => $row['item_name'],
                            'qty'                    => (float) $row['qty'],
                            'harga'                  => (float) $row['harga'],
                            'total'                  => (float) $row['qty'] * (float) $row['harga'],
                            'satuan'                 => $row['satuan'] ?? $item->satuan,
                            'hpp_per_pcs'            => $row['hpp_per_pcs'] ?? $item->hpp_per_pcs,
                            'harga_jual_indikatif'   => $row['harga_jual_indikatif'] ?? $item->harga_jual_indikatif,
                            'total_biaya_order'      => $row['total_biaya_order'] ?? $item->total_biaya_order,
                            'biaya_bahan'            => $row['biaya_bahan'] ?? $item->biaya_bahan,
                            'biaya_proses'           => $row['biaya_proses'] ?? $item->biaya_proses,
                            'kebutuhan_kertas_kg'    => $row['kebutuhan_kertas_kg'] ?? $item->kebutuhan_kertas_kg,
                            'kebutuhan_kertas_meter' => $row['kebutuhan_kertas_meter'] ?? $item->kebutuhan_kertas_meter,
                            'kalkulasi_hpp_data'     => $row['kalkulasi_hpp_data'] ?? $item->kalkulasi_hpp_data,
                        ]);
                    }
                }
            }

            $subtotal = $orderConfirmation->items()->sum('total');
            $diskon = (float) ($orderConfirmation->diskon ?? 0);
            $orderConfirmation->update([
                'subtotal'    => $subtotal,
                'grand_total' => max(0, $subtotal - $diskon),
            ]);

            return $orderConfirmation->fresh(['customer', 'items.finishGoodItem', 'approvalForm', 'salesOrders']);
        });

        return response()->json([
            'message' => "Order Confirmation {$updated->doc_no} berhasil disimpan.",
            'data'    => $updated,
        ]);
    }

    public function convertToSo(Request $request, OrderConfirmation $orderConfirmation, MarketingWorkflowService $workflow): JsonResponse
    {
        $itemId = $request->input('item_id') ? (int) $request->input('item_id') : null;
        $salesOrder = $workflow->convertOrderConfirmationToSalesOrder($orderConfirmation, $itemId);

        return response()->json([
            'message' => "Sales Order {$salesOrder->no_sales_order} berhasil diterbitkan.",
            'data'    => $salesOrder->load(['customer', 'itemable']),
        ], 201);
    }

    public function destroy(OrderConfirmation $orderConfirmation): JsonResponse
    {
        if ($orderConfirmation->salesOrders()->exists()) {
            return response()->json([
                'message' => 'Order Confirmation tidak dapat dihapus karena sudah diterbitkan menjadi Sales Order.',
            ], 422);
        }

        $orderConfirmation->delete();

        return response()->json([
            'message' => 'Order Confirmation berhasil dihapus.',
        ]);
    }
}

