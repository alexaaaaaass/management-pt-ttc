<?php

namespace App\Services\Dispatch;

use App\Models\MasterSpk;
use App\Models\SalesOrder;
use App\Models\SuratJalan;
use App\Models\SuratJalanItem;
use App\Services\Inventory\FinishGoodStockService;
use App\Services\Inventory\StockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SuratJalanService
{
    public function __construct(
        protected StockService $stockService,
        protected FinishGoodStockService $finishGoodStockService
    ) {}

    /**
     * Generate format kode surat jalan: SJ-YYYYMMDD-XXX
     */
    public function generateKodeSuratJalan(): string
    {
        $tanggal = Carbon::now()->format('Ymd');
        $count = SuratJalan::whereDate('created_at', today())->count() + 1;

        return 'SJ-' . $tanggal . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Cek apakah Surat Jalan aman untuk diubah / dihapus (belum memiliki invoice).
     */
    public function canModify(SuratJalan $suratJalan): bool
    {
        return ! $suratJalan->invoice()->exists();
    }

    /**
     * Buat Surat Jalan baru dengan validasi stok, pencatatan mutasi, dan link SPK otomatis.
     */
    public function createSuratJalan(array $data, array $detailItems = []): SuratJalan
    {
        return DB::transaction(function () use ($data, $detailItems) {
            $salesOrderId = $data['sales_order_id'] ?? null;
            if (! $salesOrderId) {
                throw ValidationException::withMessages([
                    'sales_order_id' => 'Sales Order wajib dipilih.',
                ]);
            }

            $salesOrder = SalesOrder::with(['items', 'itemable'])->findOrFail($salesOrderId);

            // Generate nomor surat jalan jika belum ada
            if (empty($data['kode_surat_jalan'])) {
                $data['kode_surat_jalan'] = $this->generateKodeSuratJalan();
            }

            // Hubungkan SPK secara otomatis jika order berbasis SPK
            $spk = null;
            if ($salesOrder->mode === 'SPK' || empty($data['spk_id'])) {
                $spk = MasterSpk::where('sales_order_id', $salesOrder->id)->first();
                if ($spk) {
                    $data['spk_id'] = $spk->id;
                }
            } elseif (!empty($data['spk_id'])) {
                $spk = MasterSpk::find($data['spk_id']);
            }

            // Validasi stok barang jadi sebelum simpan
            if ($salesOrder->mode === 'SPK') {
                $qtyKirim = (float) ($data['qty_pengiriman'] ?? 0);
                if ($qtyKirim <= 0) {
                    throw ValidationException::withMessages([
                        'qty_pengiriman' => 'Qty pengiriman harus lebih besar dari 0.',
                    ]);
                }

                $available = $this->getShippableFinishGoodStock($salesOrder, $spk);

                if ($qtyKirim > $available) {
                    throw ValidationException::withMessages([
                        'qty_pengiriman' => "Stok barang jadi tidak mencukupi untuk dikirim (Tersedia siap kirim: " . number_format($available, 0, ',', '.') . " pcs, diminta: " . number_format($qtyKirim, 0, ',', '.') . " pcs).",
                    ]);
                }
            }

            // Simpan model Surat Jalan
            $suratJalan = SuratJalan::create($data);

            // Proses pemotongan stok & detail berdasarkan mode Sales Order
            switch ($salesOrder->mode) {
                case 'SPK':
                    $fgId = $salesOrder->itemable_id;
                    $qtyKirim = (float) ($data['qty_pengiriman'] ?? 0);
                    if ($fgId && $qtyKirim > 0) {
                        $toRelease = $this->computeReservedDeltaForShipment($salesOrder, $qtyKirim, $suratJalan->id);

                        $this->finishGoodStockService->decreaseStock(
                            finishGoodItemId: (int) $fgId,
                            qty: $qtyKirim,
                            reference: $suratJalan,
                            source: 'SURAT_JALAN',
                            spkId: $suratJalan->spk_id,
                            remarks: 'Pengiriman Surat Jalan - ' . $suratJalan->kode_surat_jalan
                        );

                        if ($toRelease > 0) {
                            $this->finishGoodStockService->releaseReservedStock((int) $fgId, $toRelease);
                        }
                    }
                    break;

                case 'SINGLE_MATERIAL':
                    $item = $salesOrder->items->first();
                    $qtyKirim = (float) ($data['qty_pengiriman'] ?? 0);

                    if ($item && $qtyKirim > 0) {
                        $availableStock = $this->stockService->getAvailableStock($item->master_item_id);
                        if ($qtyKirim > $availableStock) {
                            throw ValidationException::withMessages([
                                'qty_pengiriman' => "Stok material tidak mencukupi (Tersedia: {$availableStock}, Diminta: {$qtyKirim}).",
                            ]);
                        }

                        SuratJalanItem::create([
                            'surat_jalan_id'      => $suratJalan->id,
                            'sales_order_item_id' => $item->id,
                            'master_item_id'      => $item->master_item_id,
                            'qty_kirim'           => $qtyKirim,
                        ]);

                        $this->stockService->decreaseStock(
                            itemId: $item->master_item_id,
                            qty: $qtyKirim,
                            reference: $suratJalan,
                            remarks: 'Pengiriman Material - ' . $suratJalan->kode_surat_jalan
                        );
                    }
                    break;

                case 'MULTI_MATERIAL':
                    foreach ($detailItems as $row) {
                        $qty = (float) ($row['qty_kirim'] ?? 0);
                        if ($qty <= 0) {
                            continue;
                        }

                        $masterItemId = (int) $row['master_item_id'];
                        $availableStock = $this->stockService->getAvailableStock($masterItemId);
                        if ($qty > $availableStock) {
                            throw ValidationException::withMessages([
                                'items' => "Stok material tidak mencukupi untuk item ID {$masterItemId} (Tersedia: {$availableStock}, Diminta: {$qty}).",
                            ]);
                        }

                        $detail = SuratJalanItem::create([
                            'surat_jalan_id'      => $suratJalan->id,
                            'sales_order_item_id' => $row['sales_order_item_id'],
                            'master_item_id'      => $masterItemId,
                            'qty_kirim'           => $qty,
                        ]);

                        $this->stockService->decreaseStock(
                            itemId: $masterItemId,
                            qty: $qty,
                            reference: $suratJalan,
                            remarks: 'Pengiriman Material - ' . $suratJalan->kode_surat_jalan
                        );
                    }
                    break;
            }

            return $suratJalan;
        });
    }

    /**
     * Update Surat Jalan yang ada dengan rollback stok lama dan terapkan data baru.
     */
    public function updateSuratJalan(SuratJalan $suratJalan, array $data, array $detailItems = []): SuratJalan
    {
        return DB::transaction(function () use ($suratJalan, $data, $detailItems) {
            if (! $this->canModify($suratJalan)) {
                throw new \Exception('Surat Jalan tidak dapat diedit karena sudah memiliki Invoice terkait.');
            }

            $suratJalan->loadMissing(['items', 'salesOrder.items']);

            // 1a. Rollback pemotongan stok barang jadi lama jika mode SPK
            if ($suratJalan->salesOrder?->mode === 'SPK' && $suratJalan->salesOrder?->itemable_id && (float) $suratJalan->qty_pengiriman > 0) {
                $oldFgId = (int) $suratJalan->salesOrder->itemable_id;
                $oldQty = (float) $suratJalan->qty_pengiriman;
                $toReReserve = $this->computeReservedDeltaForShipment($suratJalan->salesOrder, $oldQty, $suratJalan->id);

                $this->finishGoodStockService->increaseStock(
                    finishGoodItemId: $oldFgId,
                    qty: $oldQty,
                    reference: $suratJalan,
                    source: 'SURAT_JALAN',
                    spkId: $suratJalan->spk_id,
                    remarks: 'Rollback Update Surat Jalan - ' . $suratJalan->kode_surat_jalan
                );

                if ($toReReserve > 0) {
                    $this->finishGoodStockService->reserveStock($oldFgId, $toReReserve);
                }
            }

            // 1b. Rollback pemotongan stok material lama
            foreach ($suratJalan->items as $oldItem) {
                if ($oldItem->qty_kirim > 0 && $oldItem->master_item_id) {
                    $this->stockService->increaseStock(
                        itemId: $oldItem->master_item_id,
                        qty: (float) $oldItem->qty_kirim,
                        reference: $suratJalan,
                        remarks: 'Rollback Update Surat Jalan - ' . $suratJalan->kode_surat_jalan
                    );
                }
            }
            $suratJalan->items()->delete();

            // 2. Hubungkan SPK jika mode SPK
            $salesOrderId = $data['sales_order_id'] ?? $suratJalan->sales_order_id;
            $salesOrder = SalesOrder::with(['items', 'itemable'])->findOrFail($salesOrderId);

            $spk = null;
            if ($salesOrder->mode === 'SPK' || empty($data['spk_id'])) {
                $spk = MasterSpk::where('sales_order_id', $salesOrder->id)->first();
                if ($spk) {
                    $data['spk_id'] = $spk->id;
                }
            } elseif (!empty($data['spk_id'])) {
                $spk = MasterSpk::find($data['spk_id']);
            }

            // Validasi stok barang jadi saat update (stok lama sudah di-rollback di atas)
            if ($salesOrder->mode === 'SPK') {
                $qtyKirim = (float) ($data['qty_pengiriman'] ?? $suratJalan->qty_pengiriman);
                if ($qtyKirim <= 0) {
                    throw ValidationException::withMessages([
                        'qty_pengiriman' => 'Qty pengiriman harus lebih besar dari 0.',
                    ]);
                }

                $available = $this->getShippableFinishGoodStock(
                    $salesOrder,
                    $spk,
                    $suratJalan->id,
                    (float) $suratJalan->qty_pengiriman
                );

                if ($qtyKirim > $available) {
                    throw ValidationException::withMessages([
                        'qty_pengiriman' => "Stok barang jadi tidak mencukupi untuk dikirim (Tersedia siap kirim: " . number_format($available, 0, ',', '.') . " pcs, diminta: " . number_format($qtyKirim, 0, ',', '.') . " pcs).",
                    ]);
                }
            }

            $suratJalan->update($data);

            // 3. Terapkan pemotongan stok baru sesuai mode
            switch ($salesOrder->mode) {
                case 'SPK':
                    $fgId = $salesOrder->itemable_id;
                    $qtyKirim = (float) ($data['qty_pengiriman'] ?? $suratJalan->qty_pengiriman);
                    if ($fgId && $qtyKirim > 0) {
                        $toRelease = $this->computeReservedDeltaForShipment($salesOrder, $qtyKirim, $suratJalan->id);

                        $this->finishGoodStockService->decreaseStock(
                            finishGoodItemId: (int) $fgId,
                            qty: $qtyKirim,
                            reference: $suratJalan,
                            source: 'SURAT_JALAN',
                            spkId: $suratJalan->spk_id,
                            remarks: 'Pengiriman Surat Jalan (Update) - ' . $suratJalan->kode_surat_jalan
                        );

                        if ($toRelease > 0) {
                            $this->finishGoodStockService->releaseReservedStock((int) $fgId, $toRelease);
                        }
                    }
                    break;

                case 'SINGLE_MATERIAL':
                    $item = $salesOrder->items->first();
                    $qtyKirim = (float) ($data['qty_pengiriman'] ?? 0);

                    if ($item && $qtyKirim > 0) {
                        $availableStock = $this->stockService->getAvailableStock($item->master_item_id);
                        if ($qtyKirim > $availableStock) {
                            throw ValidationException::withMessages([
                                'qty_pengiriman' => "Stok material tidak mencukupi (Tersedia: {$availableStock}, Diminta: {$qtyKirim}).",
                            ]);
                        }

                        SuratJalanItem::create([
                            'surat_jalan_id'      => $suratJalan->id,
                            'sales_order_item_id' => $item->id,
                            'master_item_id'      => $item->master_item_id,
                            'qty_kirim'           => $qtyKirim,
                        ]);

                        $this->stockService->decreaseStock(
                            itemId: $item->master_item_id,
                            qty: $qtyKirim,
                            reference: $suratJalan,
                            remarks: 'Pengiriman Material (Update) - ' . $suratJalan->kode_surat_jalan
                        );
                    }
                    break;

                case 'MULTI_MATERIAL':
                    foreach ($detailItems as $row) {
                        $qty = (float) ($row['qty_kirim'] ?? 0);
                        if ($qty <= 0) {
                            continue;
                        }

                        $masterItemId = (int) $row['master_item_id'];
                        $availableStock = $this->stockService->getAvailableStock($masterItemId);
                        if ($qty > $availableStock) {
                            throw ValidationException::withMessages([
                                'items' => "Stok material tidak mencukupi untuk item ID {$masterItemId} (Tersedia: {$availableStock}, Diminta: {$qty}).",
                            ]);
                        }

                        SuratJalanItem::create([
                            'surat_jalan_id'      => $suratJalan->id,
                            'sales_order_item_id' => $row['sales_order_item_id'],
                            'master_item_id'      => $masterItemId,
                            'qty_kirim'           => $qty,
                        ]);

                        $this->stockService->decreaseStock(
                            itemId: $masterItemId,
                            qty: $qty,
                            reference: $suratJalan,
                            remarks: 'Pengiriman Material (Update) - ' . $suratJalan->kode_surat_jalan
                        );
                    }
                    break;
            }

            return $suratJalan;
        });
    }

    /**
     * Hapus Surat Jalan secara aman dan kembalikan stok material.
     */
    public function deleteSuratJalan(SuratJalan $suratJalan): bool
    {
        return DB::transaction(function () use ($suratJalan) {
            if (! $this->canModify($suratJalan)) {
                throw new \Exception('Surat Jalan tidak dapat dihapus karena sudah memiliki Invoice terkait.');
            }

            $suratJalan->loadMissing(['items', 'salesOrder']);

            // Kembalikan stok barang jadi jika mode SPK
            if ($suratJalan->salesOrder?->mode === 'SPK' && $suratJalan->salesOrder?->itemable_id && (float) $suratJalan->qty_pengiriman > 0) {
                $fgId = (int) $suratJalan->salesOrder->itemable_id;
                $oldQty = (float) $suratJalan->qty_pengiriman;
                $toReReserve = $this->computeReservedDeltaForShipment($suratJalan->salesOrder, $oldQty, $suratJalan->id);

                $this->finishGoodStockService->increaseStock(
                    finishGoodItemId: $fgId,
                    qty: $oldQty,
                    reference: $suratJalan,
                    source: 'SURAT_JALAN',
                    spkId: $suratJalan->spk_id,
                    remarks: 'Rollback Hapus Surat Jalan - ' . $suratJalan->kode_surat_jalan
                );

                if ($toReReserve > 0) {
                    $this->finishGoodStockService->reserveStock($fgId, $toReReserve);
                }
            }

            // Kembalikan seluruh stok material yang pernah terpotong
            foreach ($suratJalan->items as $item) {
                if ($item->qty_kirim > 0 && $item->master_item_id) {
                    $this->stockService->increaseStock(
                        itemId: $item->master_item_id,
                        qty: (float) $item->qty_kirim,
                        reference: $suratJalan,
                        remarks: 'Rollback Hapus Surat Jalan - ' . $suratJalan->kode_surat_jalan
                    );
                }
            }

            $suratJalan->items()->delete();

            return (bool) $suratJalan->delete();
        });
    }

    /**
     * Sinkronisasi data lama: Isi spk_id untuk Surat Jalan yang belum memiliki link SPK.
     */
    public function backfillSpkLinks(): int
    {
        $count = 0;
        $suratJalans = SuratJalan::whereNull('spk_id')->get();

        foreach ($suratJalans as $sj) {
            $spk = MasterSpk::where('sales_order_id', $sj->sales_order_id)->first();
            if ($spk) {
                $sj->spk_id = $spk->id;
                $sj->saveQuietly();
                $count++;
            }
        }

        return $count;
    }

    /**
     * Hitung selisih qty buffer stock (reserved) yang terpakai / harus dikembalikan
     * oleh sebuah pengiriman Surat Jalan pada Sales Order terkait.
     */
    private function computeReservedDeltaForShipment(
        SalesOrder $salesOrder,
        float $qtyKirim,
        ?int $excludeSuratJalanId = null
    ): float {
        $qtyFromStock = (float) ($salesOrder->qty_from_stock ?? 0);
        if ($qtyFromStock <= 0 || $qtyKirim <= 0) {
            return 0.0;
        }

        $otherShipped = (float) SuratJalan::where('sales_order_id', $salesOrder->id)
            ->when($excludeSuratJalanId, fn ($q) => $q->where('id', '!=', $excludeSuratJalanId))
            ->sum('qty_pengiriman');

        $consumedAfter = min($qtyFromStock, $otherShipped + $qtyKirim);
        $consumedBefore = min($qtyFromStock, $otherShipped);

        return max(0.0, $consumedAfter - $consumedBefore);
    }

    /**
     * Hitung total stok Finish Good yang boleh dikirim untuk Sales Order ini:
     * stok bebas (on_hand - reserved) ditambah sisa reserved milik SO ini sendiri.
     */
    private function getShippableFinishGoodStock(
        SalesOrder $salesOrder,
        ?MasterSpk $spk = null,
        ?int $excludeSuratJalanId = null,
        float $fallbackExtra = 0.0
    ): float {
        $fgId = $salesOrder->itemable_id;
        if (! $fgId) {
            return $spk ? (float) ($spk->on_hand_stock + $fallbackExtra) : 0.0;
        }

        $onHand = $this->finishGoodStockService->getAvailableStock((int) $fgId);
        $freeStock = $this->finishGoodStockService->getFreeBufferStock((int) $fgId);
        $qtyFromStock = (float) ($salesOrder->qty_from_stock ?? 0);

        $otherShipped = (float) SuratJalan::where('sales_order_id', $salesOrder->id)
            ->when($excludeSuratJalanId, fn ($q) => $q->where('id', '!=', $excludeSuratJalanId))
            ->sum('qty_pengiriman');

        $ownReservedRemaining = max(0.0, $qtyFromStock - $otherShipped);

        return min($onHand, $freeStock + $ownReservedRemaining);
    }
}
