<?php

namespace App\Services\PurchaseOrder;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    public function create(PurchaseOrder $purchaseOrder, array $items): void
    {
        DB::transaction(function () use ($purchaseOrder, $items) {

            foreach ($items as $item) {

                $purchaseOrder->items()->create([
                    'item_id'      => $item['item_id'],
                    'qty_pr'       => $item['qty_pr'] ?? 0,
                    'qty_po'       => $item['qty_po'],
                    'qty_received' => 0,
                    'qty_konversi' => $item['qty_konversi'] ?? 1,
                    'price'        => $item['price'] ?? 0,
                    'discount'     => $item['discount'] ?? 0,
                    'total'        => $item['total'] ?? 0,
                    'satuan'       => $item['satuan'] ?? null,
                    'eta'          => $item['eta'] ?? null,
                    'catatan'      => $item['catatan'] ?? null,
                ]);
            }

            // Kalau PO berasal dari PR, ubah status PR
            if ($purchaseOrder->purchaseRequest) {

                $purchaseOrder->purchaseRequest->update([
                    'status' => 'otorisasi',
                ]);
            }
        });
    }
}