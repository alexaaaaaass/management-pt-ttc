<?php

namespace App\Services\PurchaseOrder;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderService
{
    /**
     * Backward-compatible create method used by existing callers.
     */
    public function create(PurchaseOrder $purchaseOrder, array $items): void
    {
        DB::transaction(function () use ($purchaseOrder, $items) {
            foreach ($items as $item) {
                $qtyPo = (float) ($item['qty_po'] ?? 0);
                $price = (float) str_replace('.', '', (string) ($item['price'] ?? 0));
                $discount = (float) ($item['discount'] ?? 0);
                $total = $item['total'] ?? (($qtyPo * $price) - $discount);

                $purchaseOrder->items()->create([
                    'item_id'      => $item['item_id'],
                    'qty_pr'       => $item['qty_pr'] ?? 0,
                    'qty_po'       => $qtyPo,
                    'qty_received' => 0,
                    'qty_konversi' => $item['qty_konversi'] ?? 1,
                    'price'        => $price,
                    'discount'     => $discount,
                    'total'        => $total,
                    'satuan'       => $item['satuan'] ?? null,
                    'eta'          => $item['eta'] ?? null,
                    'catatan'      => $item['catatan'] ?? null,
                ]);
            }

            if ($purchaseOrder->purchaseRequest) {
                $purchaseOrder->purchaseRequest->update([
                    'status' => 'otorisasi',
                ]);
            }
        });
    }

    /**
     * Generate format nomor PO: PO-UGRMS-YYYYMMDD-XXX
     */
    public function generateNoPo(?string $poDate = null): string
    {
        $tanggal = $poDate ?? now();
        $date = Carbon::parse($tanggal)->format('Ymd');
        $count = PurchaseOrder::whereDate('po_date', $tanggal)->count() + 1;
        $sequence = str_pad($count, 3, '0', STR_PAD_LEFT);

        return "PO-UGRMS-{$date}-{$sequence}";
    }

    /**
     * Cek apakah Purchase Order aman untuk diubah / dihapus (belum ada penerimaan atau faktur).
     */
    public function canModify(PurchaseOrder $purchaseOrder): bool
    {
        return ! $purchaseOrder->penerimaanBarangs()->exists() && ! $purchaseOrder->fakturs()->exists();
    }

    /**
     * Buat Purchase Order baru beserta item-itemnya dan update status PR.
     */
    public function createPurchaseOrder(array $data, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $items) {
            if (empty($data['no_po'])) {
                $data['no_po'] = $this->generateNoPo($data['po_date'] ?? null);
            }

            if (! isset($data['status'])) {
                $data['status'] = 'Open';
            }

            $purchaseOrder = PurchaseOrder::create($data);

            foreach ($items as $item) {
                if (empty($item['item_id'])) {
                    continue;
                }

                $qtyPo = (float) ($item['qty_po'] ?? 0);
                $price = (float) str_replace('.', '', (string) ($item['price'] ?? 0));
                $discount = (float) ($item['discount'] ?? 0);
                $total = ($qtyPo * $price) - $discount;

                $purchaseOrder->items()->create([
                    'item_id'      => $item['item_id'],
                    'qty_pr'       => $item['qty_pr'] ?? 0,
                    'qty_po'       => $qtyPo,
                    'qty_received' => 0,
                    'qty_konversi' => $item['qty_konversi'] ?? 1,
                    'price'        => $price,
                    'discount'     => $discount,
                    'total'        => $total,
                    'satuan'       => $item['satuan'] ?? null,
                    'eta'          => $item['eta'] ?? null,
                    'catatan'      => $item['catatan'] ?? null,
                ]);
            }

            if ($purchaseOrder->purchaseRequest) {
                $purchaseOrder->purchaseRequest->update([
                    'status' => 'otorisasi',
                ]);
            }

            return $purchaseOrder;
        });
    }

    /**
     * Update Purchase Order yang ada dengan sinkronisasi item cerdas (menjaga riwayat received).
     */
    public function updatePurchaseOrder(PurchaseOrder $purchaseOrder, array $data, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $data, $items) {
            if (! $this->canModify($purchaseOrder)) {
                // Jika sudah ada penerimaan, validasi agar qty_po tidak diturunkan di bawah qty_received
                foreach ($items as $item) {
                    $existingItem = $purchaseOrder->items()->where('item_id', $item['item_id'])->first();
                    if ($existingItem && (float) ($item['qty_po'] ?? 0) < (float) $existingItem->qty_received) {
                        throw ValidationException::withMessages([
                            'items' => "Qty PO untuk item {$existingItem->item?->nama_master_item} tidak boleh lebih kecil dari yang sudah diterima ({$existingItem->qty_received}).",
                        ]);
                    }
                }
            }

            $purchaseOrder->update($data);

            $existingItems = $purchaseOrder->items()->get()->keyBy('id');
            $processedIds = [];

            foreach ($items as $item) {
                if (empty($item['item_id'])) {
                    continue;
                }

                $itemId = $item['id'] ?? null;
                $qtyPo = (float) ($item['qty_po'] ?? 0);
                $price = (float) str_replace('.', '', (string) ($item['price'] ?? 0));
                $discount = (float) ($item['discount'] ?? 0);
                $total = ($qtyPo * $price) - $discount;

                $itemData = [
                    'item_id'      => $item['item_id'],
                    'qty_pr'       => $item['qty_pr'] ?? 0,
                    'qty_po'       => $qtyPo,
                    'qty_konversi' => $item['qty_konversi'] ?? 1,
                    'price'        => $price,
                    'discount'     => $discount,
                    'total'        => $total,
                    'satuan'       => $item['satuan'] ?? null,
                    'eta'          => $item['eta'] ?? null,
                    'catatan'      => $item['catatan'] ?? null,
                ];

                if ($itemId && $existingItems->has($itemId)) {
                    $existingItem = $existingItems->get($itemId);
                    $existingItem->update($itemData);
                    $processedIds[] = $itemId;
                } else {
                    $itemData['qty_received'] = 0;
                    $newItem = $purchaseOrder->items()->create($itemData);
                    $processedIds[] = $newItem->id;
                }
            }

            // Hapus item lama yang tidak ada di form (hanya jika belum pernah diterima)
            foreach ($existingItems as $oldId => $oldItem) {
                if (! in_array($oldId, $processedIds)) {
                    if ((float) $oldItem->qty_received > 0) {
                        throw ValidationException::withMessages([
                            'items' => "Tidak dapat menghapus item {$oldItem->item?->nama_master_item} karena sudah ada barang yang diterima.",
                        ]);
                    }
                    $oldItem->delete();
                }
            }

            return $purchaseOrder;
        });
    }

    /**
     * Hapus Purchase Order secara aman.
     */
    public function deletePurchaseOrder(PurchaseOrder $purchaseOrder): bool
    {
        return DB::transaction(function () use ($purchaseOrder) {
            if (! $this->canModify($purchaseOrder)) {
                throw new \Exception('Purchase Order tidak dapat dihapus karena sudah memiliki Penerimaan Barang atau Faktur terkait.');
            }

            if ($purchaseOrder->purchaseRequest) {
                $purchaseOrder->purchaseRequest->update([
                    'status' => 'deotorisasi',
                ]);
            }

            $purchaseOrder->items()->delete();

            return (bool) $purchaseOrder->delete();
        });
    }

    /**
     * Sinkronkan status penerimaan (Open, Partial, Completed) untuk PO ini.
     */
    public function syncReceivingStatus(PurchaseOrder $purchaseOrder): string
    {
        $purchaseOrder->loadMissing('items');

        $totalPo = (float) $purchaseOrder->items->sum('qty_po');
        $totalReceived = (float) $purchaseOrder->items->sum('qty_received');

        if ($totalReceived <= 0) {
            $status = 'Open';
        } elseif ($totalReceived >= $totalPo) {
            $status = 'Completed';
        } else {
            $status = 'Partial';
        }

        $purchaseOrder->updateQuietly(['status' => $status]);

        return $status;
    }

    /**
     * Ambil data progres penerimaan PO untuk UI / badge.
     */
    public function getReceivingProgress(PurchaseOrder $purchaseOrder): array
    {
        $purchaseOrder->loadMissing('items');

        $totalPo = (float) $purchaseOrder->items->sum('qty_po');
        $totalReceived = (float) $purchaseOrder->items->sum('qty_received');
        $outstanding = max(0, $totalPo - $totalReceived);
        $percentage = $totalPo > 0 ? min(100, round(($totalReceived / $totalPo) * 100, 1)) : 0;

        $status = $purchaseOrder->status ?: 'Open';
        $color = match ($status) {
            'Completed' => 'success',
            'Partial'   => 'warning',
            default     => 'gray',
        };

        return [
            'status'         => $status,
            'color'          => $color,
            'total_po'       => $totalPo,
            'total_received' => $totalReceived,
            'outstanding'    => $outstanding,
            'percentage'     => $percentage,
        ];
    }
}