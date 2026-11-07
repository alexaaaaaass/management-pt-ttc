<?php

namespace App\Services\Inventory;

use App\Models\FinishGoodItem;
use App\Models\FinishGoodMovement;
use App\Models\FinishGoodStock;
use App\Models\Packaging;
use App\Models\SubcountInItem;
use App\Models\SuratJalan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FinishGoodStockService
{
    /**
     * Ambil stok barang jadi yang siap dikirim (on_hand).
     */
    public function getAvailableStock(int $finishGoodItemId): float
    {
        $stock = FinishGoodStock::where('finish_good_item_id', $finishGoodItemId)->first();

        if (! $stock) {
            return 0.0;
        }

        return max(0.0, (float) $stock->on_hand);
    }

    /**
     * Cek apakah stok barang jadi mencukupi untuk dikirim.
     */
    public function hasSufficientStock(int $finishGoodItemId, float $qtyRequired): bool
    {
        return $this->getAvailableStock($finishGoodItemId) >= $qtyRequired;
    }

    /**
     * Tambah stok barang jadi siap kirim (Packaging Selesai / Subcount In Barang Jadi / Rollback Surat Jalan).
     */
    public function increaseStock(
        int $finishGoodItemId,
        float $qty,
        ?Model $reference = null,
        string $source = 'PACKAGING',
        ?int $spkId = null,
        ?string $remarks = null
    ): FinishGoodStock {
        if ($qty <= 0) {
            throw new InvalidArgumentException('Quantity penambahan stok barang jadi harus lebih besar dari 0.');
        }

        return DB::transaction(function () use ($finishGoodItemId, $qty, $reference, $source, $spkId, $remarks) {
            $stock = FinishGoodStock::firstOrCreate(
                ['finish_good_item_id' => $finishGoodItemId],
                ['on_hand' => 0, 'blocked' => 0]
            );

            $stock = FinishGoodStock::where('id', $stock->id)->lockForUpdate()->first();

            $before = (float) $stock->on_hand;
            $after = round($before + $qty, 2);

            $stock->update([
                'on_hand' => $after,
            ]);

            FinishGoodMovement::create([
                'finish_good_item_id' => $finishGoodItemId,
                'spk_id'              => $spkId,
                'movement_type'       => 'IN',
                'source'              => $source,
                'qty'                 => $qty,
                'before_stock'        => $before,
                'after_stock'         => $after,
                'reference_type'      => $reference ? get_class($reference) : null,
                'reference_id'        => $reference?->getKey(),
                'user_id'             => auth()->id(),
                'remarks'             => $remarks ?: 'Penambahan Stok Barang Jadi',
            ]);

            return $stock;
        });
    }

    /**
     * Kurangi stok barang jadi (Pengiriman Surat Jalan / Rollback Packaging / Rollback Subcount In).
     */
    public function decreaseStock(
        int $finishGoodItemId,
        float $qty,
        ?Model $reference = null,
        string $source = 'SURAT_JALAN',
        ?int $spkId = null,
        ?string $remarks = null,
        bool $allowNegative = false
    ): FinishGoodStock {
        if ($qty <= 0) {
            throw new InvalidArgumentException('Quantity pengurangan stok barang jadi harus lebih besar dari 0.');
        }

        return DB::transaction(function () use ($finishGoodItemId, $qty, $reference, $source, $spkId, $remarks, $allowNegative) {
            $stock = FinishGoodStock::firstOrCreate(
                ['finish_good_item_id' => $finishGoodItemId],
                ['on_hand' => 0, 'blocked' => 0]
            );

            $stock = FinishGoodStock::where('id', $stock->id)->lockForUpdate()->first();

            $before = (float) $stock->on_hand;

            if (! $allowNegative && $qty > $before) {
                $fg = FinishGoodItem::find($finishGoodItemId);
                $nama = $fg?->nama_barang ?? "ID {$finishGoodItemId}";
                throw new InvalidArgumentException("Stok barang jadi '{$nama}' tidak mencukupi. Tersedia: " . number_format($before, 0, ',', '.') . " pcs, dibutuhkan: " . number_format($qty, 0, ',', '.') . " pcs.");
            }

            $after = round($before - $qty, 2);

            $stock->update([
                'on_hand' => $after,
            ]);

            FinishGoodMovement::create([
                'finish_good_item_id' => $finishGoodItemId,
                'spk_id'              => $spkId,
                'movement_type'       => 'OUT',
                'source'              => $source,
                'qty'                 => $qty,
                'before_stock'        => $before,
                'after_stock'         => $after,
                'reference_type'      => $reference ? get_class($reference) : null,
                'reference_id'        => $reference?->getKey(),
                'user_id'             => auth()->id(),
                'remarks'             => $remarks ?: 'Pengeluaran Stok Barang Jadi',
            ]);

            return $stock;
        });
    }

    /**
     * Catat penambahan stok blokir dari Packaging jenis 'blokir'.
     */
    public function increaseBlockedStock(
        int $finishGoodItemId,
        float $qty,
        ?Model $reference = null,
        ?int $spkId = null,
        ?string $remarks = null
    ): FinishGoodStock {
        return DB::transaction(function () use ($finishGoodItemId, $qty, $reference, $spkId, $remarks) {
            $stock = FinishGoodStock::firstOrCreate(
                ['finish_good_item_id' => $finishGoodItemId],
                ['on_hand' => 0, 'blocked' => 0]
            );

            $stock = FinishGoodStock::where('id', $stock->id)->lockForUpdate()->first();
            $stock->update([
                'blocked' => round((float) $stock->blocked + $qty, 2),
            ]);

            FinishGoodMovement::create([
                'finish_good_item_id' => $finishGoodItemId,
                'spk_id'              => $spkId,
                'movement_type'       => 'BLOCK',
                'source'              => 'PACKAGING',
                'qty'                 => $qty,
                'before_stock'        => (float) $stock->on_hand,
                'after_stock'         => (float) $stock->on_hand,
                'reference_type'      => $reference ? get_class($reference) : null,
                'reference_id'        => $reference?->getKey(),
                'user_id'             => auth()->id(),
                'remarks'             => $remarks ?: 'Barang Jadi Diblokir (QC Hold)',
            ]);

            return $stock;
        });
    }

    /**
     * Kurangi stok blokir (saat Packaging jenis 'blokir' dihapus/diedit).
     */
    public function decreaseBlockedStock(
        int $finishGoodItemId,
        float $qty,
        ?Model $reference = null,
        ?int $spkId = null,
        ?string $remarks = null
    ): FinishGoodStock {
        return DB::transaction(function () use ($finishGoodItemId, $qty, $reference, $spkId, $remarks) {
            $stock = FinishGoodStock::firstOrCreate(
                ['finish_good_item_id' => $finishGoodItemId],
                ['on_hand' => 0, 'blocked' => 0]
            );

            $stock = FinishGoodStock::where('id', $stock->id)->lockForUpdate()->first();
            $stock->update([
                'blocked' => max(0.0, round((float) $stock->blocked - $qty, 2)),
            ]);

            return $stock;
        });
    }

    /**
     * Penyesuaian stok manual (Stock Opname / Adjustment).
     */
    public function adjustStock(
        int $finishGoodItemId,
        float $newOnHand,
        ?string $remarks = null
    ): FinishGoodStock {
        return DB::transaction(function () use ($finishGoodItemId, $newOnHand, $remarks) {
            $stock = FinishGoodStock::firstOrCreate(
                ['finish_good_item_id' => $finishGoodItemId],
                ['on_hand' => 0, 'blocked' => 0]
            );

            $stock = FinishGoodStock::where('id', $stock->id)->lockForUpdate()->first();

            $before = (float) $stock->on_hand;
            $diff = round($newOnHand - $before, 2);

            $stock->update([
                'on_hand' => max(0.0, round($newOnHand, 2)),
            ]);

            if ($diff != 0.0) {
                FinishGoodMovement::create([
                    'finish_good_item_id' => $finishGoodItemId,
                    'spk_id'              => null,
                    'movement_type'       => 'ADJUSTMENT',
                    'source'              => 'MANUAL_ADJUSTMENT',
                    'qty'                 => abs($diff),
                    'before_stock'        => $before,
                    'after_stock'         => max(0.0, round($newOnHand, 2)),
                    'reference_type'      => FinishGoodStock::class,
                    'reference_id'        => $stock->id,
                    'user_id'             => auth()->id(),
                    'remarks'             => ($remarks ?: 'Penyesuaian Stok Manual') . ($diff > 0 ? " (+{$diff})" : " ({$diff})"),
                ]);
            }

            return $stock;
        });
    }

    /**
     * Sinkronisasi histori Packaging, Subcount In (Barang Jadi), dan Surat Jalan ke FinishGoodStock.
     */
    public function syncExistingStock(): int
    {
        return DB::transaction(function () {
            $count = 0;

            foreach (FinishGoodItem::all() as $fg) {
                FinishGoodStock::firstOrCreate(
                    ['finish_good_item_id' => $fg->id],
                    ['on_hand' => 0, 'blocked' => 0]
                );
                $count++;
            }

            // Backfill Packaging yang belum tercatat di FinishGoodMovement
            $packagings = Packaging::with('spk.salesOrder')->orderBy('id')->get();
            foreach ($packagings as $pkg) {
                $fgId = $pkg->spk?->salesOrder?->itemable_id;
                $itemableType = $pkg->spk?->salesOrder?->itemable_type;

                if (! $fgId || ($itemableType && $itemableType !== FinishGoodItem::class)) {
                    continue;
                }

                $alreadyRecorded = FinishGoodMovement::where('reference_type', Packaging::class)
                    ->where('reference_id', $pkg->id)
                    ->exists();

                if (! $alreadyRecorded && (float) $pkg->grand_total > 0) {
                    if ($pkg->jenis_transfer === 'blokir') {
                        $this->increaseBlockedStock(
                            finishGoodItemId: (int) $fgId,
                            qty: (float) $pkg->grand_total,
                            reference: $pkg,
                            spkId: $pkg->spk_id,
                            remarks: 'Hasil Packaging (Blokir) - ' . $pkg->kode_packaging
                        );
                    } else {
                        $this->increaseStock(
                            finishGoodItemId: (int) $fgId,
                            qty: (float) $pkg->grand_total,
                            reference: $pkg,
                            source: 'PACKAGING',
                            spkId: $pkg->spk_id,
                            remarks: 'Hasil Packaging Produksi - ' . $pkg->kode_packaging
                        );
                    }
                }
            }

            // Backfill Surat Jalan (mode SPK) yang belum tercatat di FinishGoodMovement
            $suratJalans = SuratJalan::with('salesOrder')->orderBy('id')->get();
            foreach ($suratJalans as $sj) {
                if ($sj->salesOrder?->mode !== 'SPK') {
                    continue;
                }

                $fgId = $sj->salesOrder?->itemable_id;
                if (! $fgId || (float) $sj->qty_pengiriman <= 0) {
                    continue;
                }

                $alreadyRecorded = FinishGoodMovement::where('reference_type', SuratJalan::class)
                    ->where('reference_id', $sj->id)
                    ->exists();

                if (! $alreadyRecorded) {
                    $this->decreaseStock(
                        finishGoodItemId: (int) $fgId,
                        qty: (float) $sj->qty_pengiriman,
                        reference: $sj,
                        source: 'SURAT_JALAN',
                        spkId: $sj->spk_id,
                        remarks: 'Pengiriman Surat Jalan - ' . $sj->kode_surat_jalan,
                        allowNegative: true
                    );
                }
            }

            return $count;
        });
    }
}

