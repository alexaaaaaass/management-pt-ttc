<?php

namespace App\Services\PurchaseOrder;

use App\Models\MasterMaterialPlate;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Support\MoneyHelper;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderService
{
    /**
     * Bersihkan input angka / mata uang berformat ribuan menjadi float.
     */
    protected function parseAmount(mixed $value): float
    {
        return (float) (MoneyHelper::clean($value) ?? 0.0);
    }

    /**
     * Hitung subtotal item dengan diskon persentase (%).
     */
    protected function calculateItemTotal(float $qty, float $price, float $discountPercent = 0.0): float
    {
        $discountPercent = max(0.0, min(100.0, $discountPercent));

        return round(($qty * $price) * (1 - ($discountPercent / 100)), 2);
    }

    /**
     * Backward-compatible create method used by existing callers.
     */
    public function create(PurchaseOrder $purchaseOrder, array $items): void
    {
        DB::transaction(function () use ($purchaseOrder, $items) {
            foreach ($items as $item) {
                if (empty($item['item_id'])) {
                    continue;
                }

                $qtyPr = $this->parseAmount($item['qty_pr'] ?? 0);
                $qtyPo = $this->parseAmount($item['qty_po'] ?? 0);
                $qtyKonversi = $this->parseAmount($item['qty_konversi'] ?? 0);
                if ($qtyKonversi <= 0) {
                    $qtyKonversi = $qtyPo;
                }
                $price = $this->parseAmount($item['price'] ?? 0);
                $discount = (float) ($item['discount'] ?? 0);
                $total = $this->calculateItemTotal($qtyKonversi, $price, $discount);

                $purchaseOrder->items()->create([
                    'item_id'      => $item['item_id'],
                    'qty_pr'       => $qtyPr,
                    'qty_po'       => $qtyPo,
                    'qty_received' => 0,
                    'qty_konversi' => $qtyKonversi,
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
     * Generate format nomor PO: PO-UGRMS-YYYYMMDD-XXX (aman dari tabrakan urutan dalam 1 transaksi).
     */
    public function generateNoPo(?string $poDate = null): string
    {
        $tanggal = $poDate ? Carbon::parse($poDate) : now();
        $dateStr = $tanggal->format('Ymd');
        $prefix = "PO-UGRMS-{$dateStr}-";

        $lastNoPo = PurchaseOrder::where('no_po', 'like', "{$prefix}%")
            ->orderByDesc('no_po')
            ->value('no_po');

        $nextNumber = 1;
        if ($lastNoPo && preg_match('/-(\d+)$/', $lastNoPo, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $nextNumber = PurchaseOrder::whereDate('po_date', $tanggal->toDateString())->count() + 1;
        }

        do {
            $candidate = $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
            $exists = PurchaseOrder::where('no_po', $candidate)->exists();
            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);

        return $candidate;
    }

    /**
     * Ambil ringkasan total qty yang sudah dibuatkan PO untuk sebuah Purchase Request per item_id.
     *
     * @return array<int, float> [item_id => total_qty_po]
     */
    public function getPrOrderedSummary(int $purchaseRequestId, ?int $excludePoId = null): array
    {
        return PurchaseOrderItem::query()
            ->whereHas('purchaseOrder', function ($query) use ($purchaseRequestId, $excludePoId) {
                $query->where('purchase_request_id', $purchaseRequestId);
                if ($excludePoId) {
                    $query->where('id', '!=', $excludePoId);
                }
            })
            ->selectRaw('item_id, SUM(qty_po) as total_ordered')
            ->groupBy('item_id')
            ->pluck('total_ordered', 'item_id')
            ->map(fn ($value) => (float) $value)
            ->all();
    }

    /**
     * Ambil rekomendasi supplier per item_id (prioritas dari MasterMaterialPlate, lalu PO terakhir item tsb).
     *
     * @param  array<int>  $itemIds
     * @return array<int, array{id: int, name: string}>
     */
    public function getRecommendedSuppliersForItems(array $itemIds): array
    {
        $itemIds = array_values(array_unique(array_filter(array_map('intval', $itemIds))));
        if (empty($itemIds)) {
            return [];
        }

        $result = [];

        // 1. Prioritas dari MasterMaterialPlate (untuk item [PLATE])
        $plateSuppliers = MasterMaterialPlate::with('supplier')
            ->whereIn('master_item_id', $itemIds)
            ->whereNotNull('supplier_id')
            ->get();

        foreach ($plateSuppliers as $plate) {
            if ($plate->master_item_id && $plate->supplier) {
                $result[(int) $plate->master_item_id] = [
                    'id'   => (int) $plate->supplier->id,
                    'name' => (string) $plate->supplier->nama_supplier,
                ];
            }
        }

        // 2. Fallback dari riwayat PO terakhir untuk item yang belum punya rekomendasi
        $missingIds = array_values(array_diff($itemIds, array_keys($result)));
        if (! empty($missingIds)) {
            $recentPoItems = PurchaseOrderItem::with('purchaseOrder.supplier')
                ->whereIn('item_id', $missingIds)
                ->orderByDesc('id')
                ->get();

            foreach ($recentPoItems as $poItem) {
                $id = (int) $poItem->item_id;
                if (! isset($result[$id]) && $poItem->purchaseOrder?->supplier) {
                    $result[$id] = [
                        'id'   => (int) $poItem->purchaseOrder->supplier->id,
                        'name' => (string) $poItem->purchaseOrder->supplier->nama_supplier,
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Cek apakah Purchase Order aman untuk diubah / dihapus (belum ada penerimaan, faktur, atau tagihan).
     */
    public function canModify(PurchaseOrder $purchaseOrder): bool
    {
        return ! $purchaseOrder->penerimaanBarangs()->exists()
            && ! $purchaseOrder->fakturs()->exists()
            && ! $purchaseOrder->poBillings()->exists();
    }

    /**
     * Buat 1 atau lebih Purchase Order resmi sekaligus dari 1 form Create PO (auto-split per supplier).
     *
     * @return Collection<int, PurchaseOrder>
     */
    public function createMultiSupplierPurchaseOrders(array $headerData, array $items): Collection
    {
        $defaultSupplierId = ! empty($headerData['supplier_id']) ? (int) $headerData['supplier_id'] : null;

        // Kumpulkan hanya baris aktif dengan item_id & qty_po > 0
        $activeRows = [];
        foreach ($items as $row) {
            if (empty($row['item_id'])) {
                continue;
            }

            $qtyPo = $this->parseAmount($row['qty_po'] ?? 0);
            if ($qtyPo <= 0) {
                continue;
            }

            $supplierId = ! empty($row['supplier_id']) ? (int) $row['supplier_id'] : $defaultSupplierId;
            $itemName = $row['nama_item'] ?? ('Item #' . $row['item_id']);

            if (! $supplierId) {
                throw ValidationException::withMessages([
                    'items' => "Supplier wajib dipilih untuk item \"{$itemName}\".",
                ]);
            }

            $row['_resolved_supplier_id'] = $supplierId;
            $row['_parsed_qty_po'] = $qtyPo;
            $activeRows[] = $row;
        }

        if (empty($activeRows)) {
            throw ValidationException::withMessages([
                'items' => 'Minimal satu item harus memiliki Qty PO lebih dari 0.',
            ]);
        }

        // Validasi total qty_po per item_id (termasuk baris split) tidak melampaui sisa kuota PR
        $prId = ! empty($headerData['purchase_request_id']) ? (int) $headerData['purchase_request_id'] : null;
        if ($prId) {
            $pr = PurchaseRequest::with('items.item')->find($prId);
            if ($pr) {
                $prQtyMap = [];
                $prNameMap = [];
                foreach ($pr->items as $prItem) {
                    $id = (int) $prItem->item_id;
                    $prQtyMap[$id] = ($prQtyMap[$id] ?? 0.0) + (float) $prItem->qty;
                    $prNameMap[$id] = $prItem->item?->nama_master_item ?? ("Item #{$id}");
                }

                $orderedMap = $this->getPrOrderedSummary($prId);

                $incomingQtyMap = [];
                foreach ($activeRows as $row) {
                    $id = (int) $row['item_id'];
                    $incomingQtyMap[$id] = ($incomingQtyMap[$id] ?? 0.0) + (float) $row['_parsed_qty_po'];
                }

                foreach ($incomingQtyMap as $itemId => $incomingQty) {
                    if (! array_key_exists($itemId, $prQtyMap)) {
                        continue;
                    }

                    $remaining = max(0.0, round($prQtyMap[$itemId] - ($orderedMap[$itemId] ?? 0.0), 4));
                    if (round($incomingQty, 4) > $remaining + 0.0001) {
                        $itemName = $prNameMap[$itemId] ?? ("Item #{$itemId}");
                        throw ValidationException::withMessages([
                            'items' => "Total Qty PO untuk \"{$itemName}\" (" . rtrim(rtrim(number_format($incomingQty, 2, '.', ''), '0'), '.') . ") melebihi sisa kebutuhan PR (" . rtrim(rtrim(number_format($remaining, 2, '.', ''), '0'), '.') . ').',
                        ]);
                    }
                }
            }
        }

        // Kelompokkan baris aktif berdasarkan supplier_id (mempertahankan urutan kemunculan)
        $groupedBySupplier = [];
        foreach ($activeRows as $row) {
            $supId = (int) $row['_resolved_supplier_id'];
            $groupedBySupplier[$supId][] = $row;
        }

        $isMultiSupplier = count($groupedBySupplier) > 1;
        $poDate = $headerData['po_date'] ?? now()->toDateString();
        $ppn = $this->parseAmount($headerData['ppn'] ?? 0);
        $ongkir = $isMultiSupplier ? 0.0 : $this->parseAmount($headerData['ongkir'] ?? 0);
        $dp = $isMultiSupplier ? 0.0 : $this->parseAmount($headerData['dp'] ?? 0);

        return DB::transaction(function () use ($groupedBySupplier, $headerData, $poDate, $ppn, $ongkir, $dp, $prId) {
            $created = new Collection();

            foreach ($groupedBySupplier as $supplierId => $supplierRows) {
                $poPayload = [
                    'no_po'               => $this->generateNoPo($poDate),
                    'purchase_request_id' => $prId,
                    'supplier_id'         => $supplierId,
                    'po_date'             => $poDate,
                    'po_type'             => $headerData['po_type'] ?? 'reguler',
                    'eta'                 => $headerData['eta'] ?? null,
                    'currency'            => $headerData['currency'] ?? 'IDR',
                    'ppn'                 => $ppn,
                    'ongkir'              => $ongkir,
                    'dp'                  => $dp,
                    'status'              => 'Open',
                ];

                $purchaseOrder = PurchaseOrder::create($poPayload);

                foreach ($supplierRows as $item) {
                    $qtyPr = $this->parseAmount($item['qty_pr'] ?? 0);
                    $qtyPo = (float) $item['_parsed_qty_po'];
                    $qtyKonversi = $this->parseAmount($item['qty_konversi'] ?? 0);
                    if ($qtyKonversi <= 0) {
                        $qtyKonversi = $qtyPo;
                    }
                    $price = $this->parseAmount($item['price'] ?? 0);
                    $discount = (float) ($item['discount'] ?? 0);
                    $total = $this->calculateItemTotal($qtyKonversi, $price, $discount);

                    $purchaseOrder->items()->create([
                        'item_id'      => $item['item_id'],
                        'qty_pr'       => $qtyPr,
                        'qty_po'       => $qtyPo,
                        'qty_received' => 0,
                        'qty_konversi' => $qtyKonversi,
                        'price'        => $price,
                        'discount'     => $discount,
                        'total'        => $total,
                        'satuan'       => $item['satuan'] ?? null,
                        'eta'          => $item['eta'] ?? null,
                        'catatan'      => $item['catatan'] ?? null,
                    ]);
                }

                $created->push($purchaseOrder->load(['supplier', 'items.item']));
            }

            if ($prId) {
                PurchaseRequest::whereKey($prId)->update(['status' => 'otorisasi']);
            }

            return $created;
        });
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

            if (array_key_exists('ppn', $data)) {
                $data['ppn'] = $this->parseAmount($data['ppn']);
            }
            if (array_key_exists('ongkir', $data)) {
                $data['ongkir'] = $this->parseAmount($data['ongkir']);
            }
            if (array_key_exists('dp', $data)) {
                $data['dp'] = $this->parseAmount($data['dp']);
            }

            $purchaseOrder = PurchaseOrder::create($data);

            foreach ($items as $item) {
                if (empty($item['item_id'])) {
                    continue;
                }

                $qtyPr = $this->parseAmount($item['qty_pr'] ?? 0);
                $qtyPo = $this->parseAmount($item['qty_po'] ?? 0);
                $qtyKonversi = $this->parseAmount($item['qty_konversi'] ?? 0);
                if ($qtyKonversi <= 0) {
                    $qtyKonversi = $qtyPo;
                }
                $price = $this->parseAmount($item['price'] ?? 0);
                $discount = (float) ($item['discount'] ?? 0);
                $total = $this->calculateItemTotal($qtyKonversi, $price, $discount);

                $purchaseOrder->items()->create([
                    'item_id'      => $item['item_id'],
                    'qty_pr'       => $qtyPr,
                    'qty_po'       => $qtyPo,
                    'qty_received' => 0,
                    'qty_konversi' => $qtyKonversi,
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
                    $existingItem = ! empty($item['id'])
                        ? $purchaseOrder->items()->find($item['id'])
                        : $purchaseOrder->items()->where('item_id', $item['item_id'])->first();

                    $parsedQtyPo = $this->parseAmount($item['qty_po'] ?? 0);
                    if ($existingItem && $parsedQtyPo < (float) $existingItem->qty_received) {
                        throw ValidationException::withMessages([
                            'items' => "Qty PO untuk item {$existingItem->item?->nama_master_item} tidak boleh lebih kecil dari yang sudah diterima ({$existingItem->qty_received}).",
                        ]);
                    }
                }
            }

            unset($data['pr_preview']);
            if (array_key_exists('ppn', $data)) {
                $data['ppn'] = $this->parseAmount($data['ppn']);
            }
            if (array_key_exists('ongkir', $data)) {
                $data['ongkir'] = $this->parseAmount($data['ongkir']);
            }
            if (array_key_exists('dp', $data)) {
                $data['dp'] = $this->parseAmount($data['dp']);
            }

            $purchaseOrder->update($data);

            $existingItems = $purchaseOrder->items()->get()->keyBy('id');
            $processedIds = [];

            foreach ($items as $item) {
                if (empty($item['item_id'])) {
                    continue;
                }

                $itemId = $item['id'] ?? null;
                $qtyPr = $this->parseAmount($item['qty_pr'] ?? 0);
                $qtyPo = $this->parseAmount($item['qty_po'] ?? 0);
                $qtyKonversi = $this->parseAmount($item['qty_konversi'] ?? 0);
                if ($qtyKonversi <= 0) {
                    $qtyKonversi = $qtyPo;
                }
                $price = $this->parseAmount($item['price'] ?? 0);
                $discount = (float) ($item['discount'] ?? 0);
                $total = $this->calculateItemTotal($qtyKonversi, $price, $discount);

                $itemData = [
                    'item_id'      => $item['item_id'],
                    'qty_pr'       => $qtyPr,
                    'qty_po'       => $qtyPo,
                    'qty_konversi' => $qtyKonversi,
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
     * Hapus Purchase Order secara aman (tanpa mencabut status otorisasi PR agar PR tetap bisa di-PO ulang).
     */
    public function deletePurchaseOrder(PurchaseOrder $purchaseOrder): bool
    {
        return DB::transaction(function () use ($purchaseOrder) {
            if (! $this->canModify($purchaseOrder)) {
                throw new \Exception('Purchase Order tidak dapat dihapus karena sudah memiliki Penerimaan Barang, Tagihan Supplier, atau Faktur terkait.');
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
