<?php

namespace App\Services\Inventory;

use App\Models\FinishGoodItem;
use App\Models\MasterItem;
use App\Models\MaterialMovement;
use App\Models\MaterialStock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockService
{
    /**
     * Ambil stok yang tersedia untuk dijual/dipakai (on_hand - allocation).
     */
    public function getAvailableStock(int $itemId): float
    {
        $stock = MaterialStock::where('item_id', $itemId)->first();

        if (! $stock) {
            return 0.0;
        }

        return max(0.0, (float) ($stock->on_hand - $stock->allocation));
    }

    /**
     * Cek ketersediaan stok material untuk jumlah yang dibutuhkan.
     */
    public function hasSufficientStock(int $itemId, float $qtyRequired): bool
    {
        return $this->getAvailableStock($itemId) >= $qtyRequired;
    }

    /**
     * Tambah stok (Barang Masuk / Pembelian / Retur Masuk / Pembatalan Pengiriman).
     */
    public function increaseStock(
        int $itemId,
        float $qty,
        ?Model $reference = null,
        ?string $remarks = null
    ): MaterialStock {
        if ($qty <= 0) {
            throw new InvalidArgumentException("Quantity penambahan stok harus lebih besar dari 0.");
        }

        return DB::transaction(function () use ($itemId, $qty, $reference, $remarks) {
            $stock = MaterialStock::firstOrCreate(
                ['item_id' => $itemId],
                ['on_hand' => 0, 'allocation' => 0]
            );

            // Kunci baris untuk transaksi aman (anti race condition)
            $stock = MaterialStock::where('id', $stock->id)->lockForUpdate()->first();

            $before = (float) $stock->on_hand;
            $after = $before + $qty;

            $stock->update([
                'on_hand' => $after,
            ]);

            RecordMaterialMovement::record(
                itemId: $itemId,
                movementType: 'IN',
                qty: $qty,
                before: $before,
                after: $after,
                remarks: $remarks ?: 'Penambahan Stok Material',
                referenceType: $reference ? get_class($reference) : null,
                referenceId: $reference?->getKey()
            );

            return $stock;
        });
    }

    /**
     * Kurangi stok (Pengiriman / Pemakaian Produksi / Retur Keluar).
     */
    public function decreaseStock(
        int $itemId,
        float $qty,
        ?Model $reference = null,
        ?string $remarks = null,
        bool $allowNegative = false
    ): MaterialStock {
        if ($qty <= 0) {
            throw new InvalidArgumentException("Quantity pengurangan stok harus lebih besar dari 0.");
        }

        return DB::transaction(function () use ($itemId, $qty, $reference, $remarks, $allowNegative) {
            $stock = MaterialStock::where('item_id', $itemId)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                if (! $allowNegative) {
                    $item = MasterItem::find($itemId);
                    $namaItem = $item?->nama_master_item ?? "ID {$itemId}";
                    throw new InvalidArgumentException("Stok material '{$namaItem}' belum terdaftar di gudang.");
                }

                $stock = MaterialStock::create([
                    'item_id' => $itemId,
                    'on_hand' => 0,
                    'allocation' => 0,
                ]);
            }

            $before = (float) $stock->on_hand;
            $available = $before - (float) $stock->allocation;

            if (! $allowNegative && $qty > $before) {
                $item = MasterItem::find($itemId);
                $namaItem = $item?->nama_master_item ?? "ID {$itemId}";
                throw new InvalidArgumentException("Stok '{$namaItem}' tidak mencukupi. Butuh: {$qty}, Tersedia di gudang: {$before}");
            }

            $after = $before - $qty;

            $stock->update([
                'on_hand' => $after,
            ]);

            RecordMaterialMovement::record(
                itemId: $itemId,
                movementType: 'OUT',
                qty: $qty,
                before: $before,
                after: $after,
                remarks: $remarks ?: 'Pengeluaran Stok Material',
                referenceType: $reference ? get_class($reference) : null,
                referenceId: $reference?->getKey()
            );

            return $stock;
        });
    }

    /**
     * Pindahkan stok on_hand ke alokasi subcount (Kirim Bahan ke Subcount/Makloon).
     */
    public function allocateStock(
        int $itemId,
        float $qty,
        ?Model $reference = null,
        ?string $remarks = null
    ): MaterialStock {
        if ($qty <= 0) {
            throw new InvalidArgumentException("Quantity alokasi harus lebih besar dari 0.");
        }

        return DB::transaction(function () use ($itemId, $qty, $reference, $remarks) {
            $stock = MaterialStock::where('item_id', $itemId)
                ->lockForUpdate()
                ->first();

            if (! $stock || $stock->on_hand < $qty) {
                $item = MasterItem::find($itemId);
                $namaItem = $item?->nama_master_item ?? "ID {$itemId}";
                $current = $stock?->on_hand ?? 0;
                throw new InvalidArgumentException("Stok '{$namaItem}' tidak mencukupi untuk dialokasikan ke subcount. Butuh: {$qty}, Sisa: {$current}");
            }

            $before = (float) $stock->on_hand;
            $after = $before - $qty;

            $stock->decrement('on_hand', $qty);
            $stock->increment('allocation', $qty);

            RecordMaterialMovement::record(
                itemId: $itemId,
                movementType: 'ALLOCATE',
                qty: $qty,
                before: $before,
                after: $after,
                remarks: $remarks ?: 'Alokasi Pengeluaran Bahan Subcount',
                referenceType: $reference ? get_class($reference) : null,
                referenceId: $reference?->getKey()
            );

            return $stock->fresh();
        });
    }

    /**
     * Lepas alokasi subcount saat barang kembali dari subcount vendor.
     */
    public function releaseAllocation(
        int $itemId,
        float $qty,
        ?Model $reference = null,
        ?string $remarks = null
    ): MaterialStock {
        return DB::transaction(function () use ($itemId, $qty, $reference, $remarks) {
            $stock = MaterialStock::where('item_id', $itemId)
                ->lockForUpdate()
                ->first();

            if ($stock && $stock->allocation > 0) {
                $stock->decrement('allocation', min((float)$stock->allocation, $qty));
            }

            return $stock ? $stock->fresh() : MaterialStock::firstOrCreate(['item_id' => $itemId]);
        });
    }

    /**
     * Kembalikan alokasi subcount kembali ke on_hand (Batal / Hapus Subcount Out).
     */
    public function deallocateStock(
        int $itemId,
        float $qty,
        ?Model $reference = null,
        ?string $remarks = null
    ): MaterialStock {
        if ($qty <= 0) {
            throw new InvalidArgumentException("Quantity dealokasi harus lebih besar dari 0.");
        }

        return DB::transaction(function () use ($itemId, $qty, $reference, $remarks) {
            $stock = MaterialStock::where('item_id', $itemId)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                return MaterialStock::create(['item_id' => $itemId, 'on_hand' => $qty, 'allocation' => 0]);
            }

            $before = (float) $stock->on_hand;
            $after = $before + $qty;

            $stock->increment('on_hand', $qty);
            $stock->decrement('allocation', min((float) $stock->allocation, $qty));

            RecordMaterialMovement::record(
                itemId: $itemId,
                movementType: 'IN',
                qty: $qty,
                before: $before,
                after: $after,
                remarks: $remarks ?: 'Pengembalian Alokasi Subcount Batal',
                referenceType: $reference ? get_class($reference) : null,
                referenceId: $reference?->getKey()
            );

            return $stock->fresh();
        });
    }

    /**
     * Kembalikan alokasi ke vendor jika Subcount In dibatalkan / dihapus.
     */
    public function restoreAllocation(
        int $itemId,
        float $qty
    ): MaterialStock {
        if ($qty <= 0) {
            throw new InvalidArgumentException("Quantity alokasi harus lebih besar dari 0.");
        }

        return DB::transaction(function () use ($itemId, $qty) {
            $stock = MaterialStock::where('item_id', $itemId)
                ->lockForUpdate()
                ->first();

            if ($stock) {
                $stock->increment('allocation', $qty);
            }

            return $stock ? $stock->fresh() : MaterialStock::create(['item_id' => $itemId, 'on_hand' => 0, 'allocation' => $qty]);
        });
    }

    /**
     * Validasi ketersediaan BOM untuk FinishGoodItem tertentu terhadap Qty Order.
     * Mengembalikan list material yang kurang (jika ada).
     */
    public function checkBomShortages(int $finishGoodItemId, float $orderQty): array
    {
        $finishGood = FinishGoodItem::with('materials.material')->find($finishGoodItemId);

        if (! $finishGood || ! $finishGood->materials) {
            return [];
        }

        $shortages = [];

        foreach ($finishGood->materials as $bom) {
            $kebutuhan = $orderQty * (float) $bom->qty;
            $available = $this->getAvailableStock($bom->master_item_id);

            if ($kebutuhan > $available) {
                $shortages[] = [
                    'material_id'   => $bom->master_item_id,
                    'nama_material' => $bom->material?->nama_master_item ?? 'Material #' . $bom->master_item_id,
                    'kebutuhan'     => $kebutuhan,
                    'tersedia'      => $available,
                    'kekurangan'    => $kebutuhan - $available,
                ];
            }
        }

        return $shortages;
    }
}
