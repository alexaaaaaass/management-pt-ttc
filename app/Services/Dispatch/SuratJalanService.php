<?php

namespace App\Services\Dispatch;

use App\Models\MasterSpk;
use App\Models\SalesOrder;
use App\Models\SuratJalan;
use App\Models\SuratJalanItem;
use App\Services\Inventory\StockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SuratJalanService
{
    public function __construct(
        protected StockService $stockService
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
            if ($salesOrder->mode === 'SPK' || empty($data['spk_id'])) {
                $spk = MasterSpk::where('sales_order_id', $salesOrder->id)->first();
                if ($spk) {
                    $data['spk_id'] = $spk->id;
                }
            }

            // Simpan model Surat Jalan
            $suratJalan = SuratJalan::create($data);

            // Proses pemotongan stok & detail berdasarkan mode Sales Order
            switch ($salesOrder->mode) {
                case 'SPK':
                    $qtyKirim = (float) ($data['qty_pengiriman'] ?? 0);
                    if ($qtyKirim <= 0) {
                        throw ValidationException::withMessages([
                            'qty_pengiriman' => 'Qty pengiriman SPK harus lebih besar dari 0.',
                        ]);
                    }
                    // on_hand_stock pada MasterSpk otomatis terhitung melalui relasi suratJalans
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

            // 1. Rollback pemotongan stok material lama
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

            if ($salesOrder->mode === 'SPK' || empty($data['spk_id'])) {
                $spk = MasterSpk::where('sales_order_id', $salesOrder->id)->first();
                if ($spk) {
                    $data['spk_id'] = $spk->id;
                }
            }

            $suratJalan->update($data);

            // 3. Terapkan pemotongan stok baru sesuai mode
            switch ($salesOrder->mode) {
                case 'SPK':
                    // On-hand stock SPK otomatis tersinkron
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

            $suratJalan->loadMissing('items');

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
}
