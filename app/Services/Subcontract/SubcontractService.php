<?php

namespace App\Services\Subcontract;

use App\Models\MasterItem;
use App\Models\SubcountIn;
use App\Models\SubcountInItem;
use App\Models\SubcountOut;
use App\Models\SubcountOutItem;
use App\Services\Inventory\FinishGoodStockService;
use App\Services\Inventory\StockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubcontractService
{
    public function __construct(
        protected StockService $stockService,
        protected FinishGoodStockService $finishGoodStockService
    ) {}

    /**
     * Generate Nomor Surat Jalan Subcount Out (SUB-OUT/m-Y/000001)
     */
    public function generateSubcountOutNumber(?Carbon $date = null): string
    {
        $date = $date ?? now();
        $period = $date->format('m-Y');

        $last = SubcountOut::where('surat_jalan_subcount', 'like', "SUB-OUT/{$period}/%")
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($last && preg_match('/SUB-OUT\/' . preg_quote($period, '/') . '\/(\d+)$/', $last->surat_jalan_subcount, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $count = SubcountOut::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $nextNumber = $count + 1;
        }

        return 'SUB-OUT/' . $period . '/' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate Nomor Subcount In (SUB-IN/m-Y/000001)
     */
    public function generateSubcountInNumber(?Carbon $date = null): string
    {
        $date = $date ?? now();
        $period = $date->format('m-Y');

        $last = SubcountIn::where('no_subcount', 'like', "SUB-IN/{$period}/%")
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($last && preg_match('/SUB-IN\/' . preg_quote($period, '/') . '\/(\d+)$/', $last->no_subcount, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $count = SubcountIn::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $nextNumber = $count + 1;
        }

        return 'SUB-IN/' . $period . '/' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Hitung sisa quantity yang belum diterima dari Subcount Out tertentu
     */
    public function calculateOutstandingQty(int $subcountOutId, ?int $itemAsalId = null, ?int $spkId = null): float
    {
        $outQuery = SubcountOutItem::where('subcount_out_id', $subcountOutId);
        if ($itemAsalId) {
            $outQuery->where('item_id', $itemAsalId);
        } elseif ($spkId) {
            $outQuery->where('spk_id', $spkId);
        }
        $totalSent = (float) $outQuery->sum('qty');

        $inQuery = SubcountInItem::where('subcount_out_id', $subcountOutId);
        if ($itemAsalId) {
            $inQuery->where('item_asal_id', $itemAsalId);
        } elseif ($spkId) {
            $inQuery->where('spk_id', $spkId);
        }
        $totalReceived = (float) $inQuery->sum('qty_diterima');

        return max(0.0, round($totalSent - $totalReceived, 2));
    }

    /**
     * Buat transaksi Subcount Out dan alokasikan stok material
     */
    public function createSubcountOut(array $data): SubcountOut
    {
        return DB::transaction(function () use ($data) {
            $itemsData = $data['items'] ?? [];
            if (empty($itemsData)) {
                throw ValidationException::withMessages([
                    'items' => 'Form items Subcount Out tidak boleh kosong.',
                ]);
            }

            // Validasi ketersediaan stok terlebih dahulu untuk semua item material
            foreach ($itemsData as $index => $item) {
                $sumberItem = $item['sumber_item'] ?? null;
                $itemId = $item['item_id'] ?? null;
                $qty = (float) ($item['qty'] ?? 0);

                if ($sumberItem === 'material' && $itemId) {
                    if ($qty <= 0) {
                        throw ValidationException::withMessages([
                            "items.{$index}.qty" => 'Quantity material harus lebih besar dari 0.',
                        ]);
                    }

                    if (! $this->stockService->hasSufficientStock($itemId, $qty)) {
                        $material = MasterItem::find($itemId);
                        $nama = $material?->nama_master_item ?? "Item #{$itemId}";
                        $available = $this->stockService->getAvailableStock($itemId);
                        throw ValidationException::withMessages([
                            "items.{$index}.qty" => "Stok material '{$nama}' tidak mencukupi untuk dikirim ke subcount. Butuh: {$qty}, Tersedia: {$available}.",
                        ]);
                    }
                }
            }

            if (empty($data['surat_jalan_subcount']) || $data['surat_jalan_subcount'] === 'AUTO') {
                $data['surat_jalan_subcount'] = $this->generateSubcountOutNumber();
            }

            unset($data['items']);
            /** @var SubcountOut $subcountOut */
            $subcountOut = SubcountOut::create($data);

            // Simpan items dan lakukan alokasi stok
            foreach ($itemsData as $item) {
                $createdItem = $subcountOut->items()->create($item);

                if (($item['sumber_item'] ?? null) === 'material' && !empty($item['item_id']) && (float) $item['qty'] > 0) {
                    $this->stockService->allocateStock(
                        itemId: (int) $item['item_id'],
                        qty: (float) $item['qty'],
                        reference: $subcountOut,
                        remarks: 'Alokasi Subcount Out - ' . $subcountOut->surat_jalan_subcount
                    );
                }
            }

            return $subcountOut->load('items');
        });
    }

    /**
     * Hapus Subcount Out dengan proteksi riwayat penerimaan dan rollback alokasi stok
     */
    public function deleteSubcountOut(SubcountOut $subcountOut): bool
    {
        return DB::transaction(function () use ($subcountOut) {
            if ($subcountOut->subcountInItems()->exists()) {
                throw new \Exception('Tidak dapat menghapus Subcount Out karena sudah memiliki catatan penerimaan (Subcount In).');
            }

            $subcountOut->loadMissing('items');

            // Kembalikan alokasi material ke on_hand
            foreach ($subcountOut->items as $item) {
                if ($item->sumber_item === 'material' && $item->item_id && (float) $item->qty > 0) {
                    $this->stockService->deallocateStock(
                        itemId: (int) $item->item_id,
                        qty: (float) $item->qty,
                        reference: $subcountOut,
                        remarks: 'Rollback Hapus Subcount Out - ' . $subcountOut->surat_jalan_subcount
                    );
                }
            }

            $subcountOut->items()->delete();
            return (bool) $subcountOut->delete();
        });
    }

    /**
     * Cek apakah Subcount Out dapat dihapus
     */
    public function canDeleteSubcountOut(SubcountOut $subcountOut): bool
    {
        return ! $subcountOut->subcountInItems()->exists();
    }

    /**
     * Buat transaksi Subcount In, tambah stok item hasil dan lepas alokasi item asal
     */
    public function createSubcountIn(array $data): SubcountIn
    {
        return DB::transaction(function () use ($data) {
            $itemsData = $data['items'] ?? [];
            if (empty($itemsData)) {
                throw ValidationException::withMessages([
                    'items' => 'Item Subcount In tidak boleh kosong.',
                ]);
            }

            // Validasi over-receiving untuk setiap item
            foreach ($itemsData as $index => $item) {
                $qtyDiterima = (float) ($item['qty_diterima'] ?? 0);
                if ($qtyDiterima <= 0) {
                    throw ValidationException::withMessages([
                        "items.{$index}.qty_diterima" => 'Quantity diterima harus lebih besar dari 0.',
                    ]);
                }

                $subcountOutId = $item['subcount_out_id'] ?? $data['subcount_out_id'] ?? null;
                $itemAsalId = !empty($item['item_asal_id']) ? (int) $item['item_asal_id'] : null;
                $spkId = !empty($item['spk_id']) ? (int) $item['spk_id'] : null;

                if ($subcountOutId && ($itemAsalId || $spkId)) {
                    $outstanding = $this->calculateOutstandingQty((int) $subcountOutId, $itemAsalId, $spkId);
                    if ($qtyDiterima > $outstanding) {
                        $namaAsal = $item['nama_item_asal'] ?? null;
                        if (! $namaAsal && $itemAsalId) {
                            $materialAsal = MasterItem::find($itemAsalId);
                            $namaAsal = $materialAsal?->nama_master_item ?? "Item #{$itemAsalId}";
                        }
                        $namaAsal = $namaAsal ?: 'Item Subcount';
                        throw ValidationException::withMessages([
                            "items.{$index}.qty_diterima" => "Qty diterima ({$qtyDiterima}) melebihi sisa yang belum diterima ({$outstanding}) untuk '{$namaAsal}'.",
                        ]);
                    }
                }
            }

            if (empty($data['no_subcount']) || $data['no_subcount'] === 'AUTO') {
                $data['no_subcount'] = $this->generateSubcountInNumber();
            }

            unset($data['items']);
            /** @var SubcountIn $subcountIn */
            $subcountIn = SubcountIn::create($data);

            // Simpan items dan mutasi stok
            foreach ($itemsData as $item) {
                $qtyDiterima = (float) ($item['qty_diterima'] ?? 0);
                $tipeHasil = $item['tipe_hasil'] ?? 'material';
                $itemHasilId = !empty($item['item_hasil_id']) ? (int) $item['item_hasil_id'] : null;
                $finishGoodItemId = !empty($item['finish_good_item_id']) ? (int) $item['finish_good_item_id'] : null;
                $spkId = !empty($item['spk_id']) ? (int) $item['spk_id'] : null;
                $itemAsalId = !empty($item['item_asal_id']) ? (int) $item['item_asal_id'] : null;

                $item['tipe_hasil'] = $tipeHasil;
                $item['item_hasil_id'] = $tipeHasil === 'material' ? $itemHasilId : null;
                $item['finish_good_item_id'] = $tipeHasil === 'finish_good' ? $finishGoodItemId : null;

                $createdItem = $subcountIn->items()->create($item);

                if ($tipeHasil === 'finish_good' && $finishGoodItemId && $qtyDiterima > 0) {
                    // Tambah stok Barang Jadi (Finish Good Stock)
                    $this->finishGoodStockService->increaseStock(
                        finishGoodItemId: $finishGoodItemId,
                        qty: $qtyDiterima,
                        reference: $subcountIn,
                        source: 'SUBCOUNT_IN',
                        spkId: $spkId,
                        remarks: 'Penerimaan Subcount In (Barang Jadi) - ' . $subcountIn->no_subcount
                    );
                } elseif ($tipeHasil === 'material' && $itemHasilId && $qtyDiterima > 0) {
                    // Tambah stok Material Produksi (Material Stock)
                    $this->stockService->increaseStock(
                        itemId: $itemHasilId,
                        qty: $qtyDiterima,
                        reference: $subcountIn,
                        remarks: 'Penerimaan Subcount In - ' . $subcountIn->no_subcount
                    );
                }

                // Lepas alokasi dari item asal
                if ($itemAsalId && $qtyDiterima > 0) {
                    $this->stockService->releaseAllocation(
                        itemId: $itemAsalId,
                        qty: $qtyDiterima,
                        reference: $subcountIn,
                        remarks: 'Pelepasan Alokasi Subcount In - ' . $subcountIn->no_subcount
                    );
                }
            }

            return $subcountIn->load('items');
        });
    }

    /**
     * Hapus Subcount In dengan rollback mutasi stok item hasil dan alokasi item asal
     */
    public function deleteSubcountIn(SubcountIn $subcountIn): bool
    {
        return DB::transaction(function () use ($subcountIn) {
            $subcountIn->loadMissing('items');

            foreach ($subcountIn->items as $item) {
                $qtyDiterima = (float) ($item->qty_diterima ?? 0);
                $tipeHasil = $item->tipe_hasil ?: 'material';

                if ($tipeHasil === 'finish_good' && $item->finish_good_item_id && $qtyDiterima > 0) {
                    $this->finishGoodStockService->decreaseStock(
                        finishGoodItemId: (int) $item->finish_good_item_id,
                        qty: $qtyDiterima,
                        reference: $subcountIn,
                        source: 'SUBCOUNT_IN',
                        spkId: $item->spk_id,
                        remarks: 'Rollback Hapus Subcount In - ' . $subcountIn->no_subcount,
                        allowNegative: true
                    );
                } elseif ($item->item_hasil_id && $qtyDiterima > 0) {
                    // Kurangi stok item hasil yang sebelumnya ditambahkan
                    $this->stockService->decreaseStock(
                        itemId: (int) $item->item_hasil_id,
                        qty: $qtyDiterima,
                        reference: $subcountIn,
                        remarks: 'Rollback Hapus Subcount In - ' . $subcountIn->no_subcount,
                        allowNegative: true
                    );
                }

                // Kembalikan alokasi vendor untuk item asal
                if ($item->item_asal_id && $qtyDiterima > 0) {
                    $this->stockService->restoreAllocation(
                        itemId: (int) $item->item_asal_id,
                        qty: $qtyDiterima
                    );
                }
            }

            $subcountIn->items()->delete();
            return (bool) $subcountIn->delete();
        });
    }

    /**
     * Cek apakah Subcount In dapat dihapus
     */
    public function canDeleteSubcountIn(SubcountIn $subcountIn): bool
    {
        return true;
    }
}
