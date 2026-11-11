<?php

namespace App\Services\PurchaseOrder;

use App\Models\PenerimaanBarang;
use App\Models\PenerimaanBarangItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Services\Inventory\StockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PenerimaanBarangService
{
    public function __construct(
        protected StockService $stockService,
        protected PurchaseOrderService $purchaseOrderService
    ) {}

    /**
     * Generate format nomor Penerimaan Barang: PB-YYYYMMDD-XXX
     */
    public function generateNoPenerimaan(): string
    {
        $date = now()->format('Ymd');
        $count = PenerimaanBarang::whereDate('created_at', now())->count() + 1;

        return 'PB-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Cek apakah Penerimaan Barang aman untuk dimodifikasi atau dihapus (tidak memiliki retur).
     */
    public function canModify(PenerimaanBarang $pb): bool
    {
        return ! $pb->externalReturns()->exists();
    }

    /**
     * Buat Penerimaan Barang baru, tambah stok fisik via StockService, catat mutasi, dan sinkronisasi status PO.
     */
    public function createPenerimaanBarang(array $data, array $items): PenerimaanBarang
    {
        return DB::transaction(function () use ($data, $items) {
            $poId = $data['purchase_order_id'] ?? null;
            if (! $poId) {
                throw ValidationException::withMessages([
                    'purchase_order_id' => 'Purchase Order wajib dipilih.',
                ]);
            }

            $purchaseOrder = PurchaseOrder::with(['items.item'])->findOrFail($poId);

            if (empty($data['no_penerimaan'])) {
                $data['no_penerimaan'] = $this->generateNoPenerimaan();
            }

            // Validasi sisa kuantitas PO yang boleh diterima
            $itemsToReceive = [];
            foreach ($items as $row) {
                $poItemId = $row['purchase_order_item_id'] ?? null;
                $qtyTerima = (float) ($row['qty_terima'] ?? 0);

                if (! $poItemId || $qtyTerima <= 0) {
                    continue;
                }

                $poItem = $purchaseOrder->items->firstWhere('id', $poItemId);
                if (! $poItem) {
                    continue;
                }

                $sisaBolehTerima = (float) $poItem->qty_po - (float) $poItem->qty_received;
                if ($qtyTerima > $sisaBolehTerima) {
                    $namaItem = $poItem->item?->nama_master_item ?? "Item ID {$poItem->item_id}";
                    throw ValidationException::withMessages([
                        'items' => "Qty terima ({$qtyTerima}) melebihi sisa PO yang belum diterima ({$sisaBolehTerima}) untuk {$namaItem}.",
                    ]);
                }

                $itemsToReceive[] = [
                    'poItem'    => $poItem,
                    'row'       => $row,
                    'qtyTerima' => $qtyTerima,
                ];
            }

            if (empty($itemsToReceive)) {
                throw ValidationException::withMessages([
                    'items' => 'Minimal 1 item dengan qty terima lebih dari 0 harus dimasukkan.',
                ]);
            }

            $pb = PenerimaanBarang::create($data);

            foreach ($itemsToReceive as $entry) {
                $poItem = $entry['poItem'];
                $row = $entry['row'];
                $qtyTerima = $entry['qtyTerima'];

                PenerimaanBarangItem::create([
                    'penerimaan_barang_id'   => $pb->id,
                    'purchase_order_item_id' => $poItem->id,
                    'qty_terima'             => $qtyTerima,
                    'tgl_exp'                => $row['tgl_exp'] ?? null,
                    'no_lot'                 => $row['no_lot'] ?? null,
                    'catatan_item'           => $row['catatan_item'] ?? null,
                ]);

                // Tambah stok fisik & catat audit mutasi masuk
                $this->stockService->increaseStock(
                    itemId: $poItem->item_id,
                    qty: $qtyTerima,
                    reference: $pb,
                    remarks: 'Penerimaan Barang PO - ' . ($pb->no_penerimaan ?: $pb->no_surat_jalan)
                );

                // Update qty_received di PO item
                $poItem->increment('qty_received', $qtyTerima);
            }

            // Sinkronkan status PO (Open, Partial, Completed)
            $this->purchaseOrderService->syncReceivingStatus($purchaseOrder);

            return $pb;
        });
    }

    /**
     * Update Penerimaan Barang: Rollback stok lama & decrement qty_received PO, lalu simpan data baru.
     */
    public function updatePenerimaanBarang(PenerimaanBarang $pb, array $data, array $items): PenerimaanBarang
    {
        return DB::transaction(function () use ($pb, $data, $items) {
            if (! $this->canModify($pb)) {
                throw new \Exception('Penerimaan Barang tidak dapat diedit karena sudah memiliki Retur Eksternal terkait.');
            }

            $pb->loadMissing(['items.purchaseOrderItem', 'purchaseOrder.items']);

            // 1. Rollback penerimaan lama
            foreach ($pb->items as $oldItem) {
                $poItem = $oldItem->purchaseOrderItem;
                if ($poItem && (float) $oldItem->qty_terima > 0) {
                    $this->stockService->decreaseStock(
                        itemId: $poItem->item_id,
                        qty: (float) $oldItem->qty_terima,
                        reference: $pb,
                        remarks: 'Rollback Edit Penerimaan Barang - ' . $pb->no_penerimaan
                    );
                    $poItem->decrement('qty_received', $oldItem->qty_terima);
                }
            }
            $pb->items()->delete();

            // 2. Update data parent
            $pb->update($data);

            $purchaseOrder = $pb->purchaseOrder->fresh('items');

            // 3. Simpan item baru & tambah stok
            foreach ($items as $row) {
                $poItemId = $row['purchase_order_item_id'] ?? null;
                $qtyTerima = (float) ($row['qty_terima'] ?? 0);

                if (! $poItemId || $qtyTerima <= 0) {
                    continue;
                }

                $poItem = $purchaseOrder->items->firstWhere('id', $poItemId);
                if (! $poItem) {
                    continue;
                }

                PenerimaanBarangItem::create([
                    'penerimaan_barang_id'   => $pb->id,
                    'purchase_order_item_id' => $poItem->id,
                    'qty_terima'             => $qtyTerima,
                    'tgl_exp'                => $row['tgl_exp'] ?? null,
                    'no_lot'                 => $row['no_lot'] ?? null,
                    'catatan_item'           => $row['catatan_item'] ?? null,
                ]);

                $this->stockService->increaseStock(
                    itemId: $poItem->item_id,
                    qty: $qtyTerima,
                    reference: $pb,
                    remarks: 'Penerimaan Barang (Update) - ' . $pb->no_penerimaan
                );

                $poItem->increment('qty_received', $qtyTerima);
            }

            // Sinkronkan status PO
            $this->purchaseOrderService->syncReceivingStatus($purchaseOrder);

            return $pb;
        });
    }

    /**
     * Hapus Penerimaan Barang: Rollback stok fisik & decrement qty_received PO.
     */
    public function deletePenerimaanBarang(PenerimaanBarang $pb): bool
    {
        return DB::transaction(function () use ($pb) {
            if (! $this->canModify($pb)) {
                throw new \Exception('Penerimaan Barang tidak dapat dihapus karena sudah memiliki Retur Eksternal terkait.');
            }

            $pb->loadMissing(['items.purchaseOrderItem', 'purchaseOrder']);

            foreach ($pb->items as $item) {
                $poItem = $item->purchaseOrderItem;
                if ($poItem && (float) $item->qty_terima > 0) {
                    $this->stockService->decreaseStock(
                        itemId: $poItem->item_id,
                        qty: (float) $item->qty_terima,
                        reference: $pb,
                        remarks: 'Rollback Hapus Penerimaan Barang - ' . $pb->no_penerimaan
                    );
                    $poItem->decrement('qty_received', $item->qty_terima);
                }
            }

            $purchaseOrder = $pb->purchaseOrder;
            $pb->items()->delete();
            $result = (bool) $pb->delete();

            if ($purchaseOrder) {
                $this->purchaseOrderService->syncReceivingStatus($purchaseOrder);
            }

            return $result;
        });
    }
}
