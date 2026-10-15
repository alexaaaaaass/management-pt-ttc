<?php

namespace App\Services\Sales;

use App\Models\FinishGoodItem;
use App\Models\MasterItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Services\Inventory\StockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesOrderService
{
    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * Generate format nomor Sales Order otomatis: 0001/IK-10/mY
     */
    public function generateNoSalesOrder(): string
    {
        $nextId = (SalesOrder::withTrashed()->max('id') ?? 0) + 1;
        $bulanTahun = Carbon::now()->format('mY');

        return str_pad($nextId, 4, '0', STR_PAD_LEFT) . '/IK-10/' . $bulanTahun;
    }

    /**
     * Cek apakah Sales Order aman untuk diubah / dihapus (tidak memiliki Surat Jalan atau SPK).
     */
    public function canModify(SalesOrder $salesOrder): bool
    {
        return ! $salesOrder->suratJalans()->exists() && ! $salesOrder->spk()->exists();
    }

    /**
     * Pengecekan ketersediaan bahan baku BOM untuk item Finish Good tertentu.
     */
    public function checkBomShortages(int $finishGoodItemId, float $orderQty): array
    {
        return $this->stockService->checkBomShortages($finishGoodItemId, $orderQty);
    }

    /**
     * Ambil data preview Bill of Material untuk Finish Good & Qty tertentu.
     */
    public function getBomPreview(int $finishGoodItemId, float $orderQty): array
    {
        $item = FinishGoodItem::with([
            'materials.material.satuan',
            'materials.departemen',
        ])->find($finishGoodItemId);

        if (! $item) {
            return [];
        }

        $bom = [];
        foreach ($item->materials as $mat) {
            $qtyMaster = (float) ($mat->qty ?? 0);
            $qtyProduksi = $orderQty * $qtyMaster;

            $bom[] = [
                'nama_material' => $mat->material?->nama_master_item ?? '-',
                'departemen'    => $mat->departemen?->nama_departemen ?? '-',
                'qty_master'    => $qtyMaster,
                'qty_produksi'  => round($qtyProduksi, 2),
                'satuan'        => strtoupper($mat->material?->satuan?->nama_satuan ?? ''),
                'waste'         => $mat->waste ?? 0,
            ];
        }

        return $bom;
    }

    /**
     * Buat Sales Order baru dengan validasi BOM dan pembuatan item material otomatis.
     */
    public function createSalesOrder(array $data, array $materialItems = []): SalesOrder
    {
        return DB::transaction(function () use ($data, $materialItems) {
            $orderType = $data['order_type'] ?? 'SPK';

            if ($orderType === 'SPK') {
                $finishGoodId = (int) ($data['itemable_id'] ?? 0);
                $qty = (float) ($data['qty'] ?? 0);

                if (! $finishGoodId) {
                    throw ValidationException::withMessages([
                        'item_source' => 'Item Finish Good wajib dipilih untuk pesanan SPK.',
                    ]);
                }

                if ($qty <= 0) {
                    throw ValidationException::withMessages([
                        'qty' => 'Jumlah pesanan harus lebih besar dari 0.',
                    ]);
                }

                // Validasi kekurangan bahan BOM
                $shortages = $this->checkBomShortages($finishGoodId, $qty);
                if (! empty($shortages)) {
                    $first = $shortages[0];
                    throw ValidationException::withMessages([
                        'qty' => "Stock tidak cukup untuk material: {$first['nama_material']} | Butuh: {$first['kebutuhan']} | Sisa: {$first['tersedia']}",
                    ]);
                }

                $data['itemable_type'] = FinishGoodItem::class;
                $data['itemable_id'] = $finishGoodId;
            } else {
                // MATERIAL mode
                $data['itemable_type'] = null;
                $data['itemable_id'] = null;
                $data['qty'] = null;
                $data['harga_pcs'] = null;
                $data['kode_material'] = null;

                if (empty($materialItems)) {
                    throw ValidationException::withMessages([
                        'items' => 'Minimal 1 material harus ditambahkan.',
                    ]);
                }
            }

            if (empty($data['no_sales_order'])) {
                $data['no_sales_order'] = $this->generateNoSalesOrder();
            }

            $salesOrder = SalesOrder::create($data);

            if ($orderType === 'MATERIAL') {
                foreach ($materialItems as $item) {
                    if (empty($item['master_item_id'])) {
                        continue;
                    }

                    SalesOrderItem::create([
                        'sales_order_id' => $salesOrder->id,
                        'master_item_id' => $item['master_item_id'],
                        'qty'            => (float) ($item['qty'] ?? 0),
                        'harga'          => (float) str_replace('.', '', (string) ($item['harga'] ?? 0)),
                    ]);
                }
            }

            return $salesOrder;
        });
    }

    /**
     * Update Sales Order yang ada.
     */
    public function updateSalesOrder(SalesOrder $salesOrder, array $data, array $materialItems = []): SalesOrder
    {
        return DB::transaction(function () use ($salesOrder, $data, $materialItems) {
            $orderType = $data['order_type'] ?? $salesOrder->order_type ?? 'SPK';

            if ($orderType === 'SPK') {
                $finishGoodId = (int) ($data['itemable_id'] ?? $salesOrder->itemable_id);
                $qty = (float) ($data['qty'] ?? $salesOrder->qty);

                if (! empty($finishGoodId) && $qty > 0) {
                    $shortages = $this->checkBomShortages($finishGoodId, $qty);
                    if (! empty($shortages)) {
                        $first = $shortages[0];
                        throw ValidationException::withMessages([
                            'qty' => "Stock tidak cukup untuk material: {$first['nama_material']} | Butuh: {$first['kebutuhan']} | Sisa: {$first['tersedia']}",
                        ]);
                    }
                }

                $data['itemable_type'] = FinishGoodItem::class;
                $data['itemable_id'] = $finishGoodId;
            } else {
                $data['itemable_type'] = null;
                $data['itemable_id'] = null;
                $data['qty'] = null;
                $data['harga_pcs'] = null;
                $data['kode_material'] = null;
            }

            $salesOrder->update($data);

            if ($orderType === 'MATERIAL') {
                $salesOrder->items()->delete();
                foreach ($materialItems as $item) {
                    if (empty($item['master_item_id'])) {
                        continue;
                    }

                    SalesOrderItem::create([
                        'sales_order_id' => $salesOrder->id,
                        'master_item_id' => $item['master_item_id'],
                        'qty'            => (float) ($item['qty'] ?? 0),
                        'harga'          => (float) str_replace('.', '', (string) ($item['harga'] ?? 0)),
                    ]);
                }
            }

            return $salesOrder;
        });
    }

    /**
     * Hapus Sales Order secara aman.
     */
    public function deleteSalesOrder(SalesOrder $salesOrder): bool
    {
        return DB::transaction(function () use ($salesOrder) {
            if (! $this->canModify($salesOrder)) {
                throw new \Exception('Sales Order tidak dapat dihapus karena sudah memiliki Surat Jalan atau SPK terkait.');
            }

            $salesOrder->items()->delete();

            return (bool) $salesOrder->delete();
        });
    }

    /**
     * Hitung total finansial Sales Order.
     */
    public function calculateTotals(SalesOrder $salesOrder): array
    {
        $salesOrder->loadMissing('items');

        if ($salesOrder->mode === 'SPK') {
            $subtotal = (float) ($salesOrder->qty ?? 0) * (float) ($salesOrder->harga_pcs ?? 0);
        } else {
            $subtotal = (float) $salesOrder->items->sum(function ($item) {
                return (float) ($item->qty ?? 0) * (float) ($item->harga ?? 0);
            });
        }

        $diskon = (float) ($salesOrder->diskon ?? 0);
        $ongkir = (float) ($salesOrder->harga_kirim ?? 0);
        $grandTotal = max(0, $subtotal - $diskon) + $ongkir;

        return [
            'subtotal'    => $subtotal,
            'diskon'      => $diskon,
            'ongkir'      => $ongkir,
            'grand_total' => $grandTotal,
        ];
    }

    /**
     * Ambil status pemenuhan pengiriman (Fulfillment Status).
     */
    public function getFulfillmentStatus(SalesOrder $salesOrder): array
    {
        $orderQty = (float) $salesOrder->order_qty;
        $deliveredQty = (float) $salesOrder->delivered_qty;
        $outstandingQty = (float) $salesOrder->outstanding_qty;

        if ($deliveredQty <= 0) {
            $status = 'BELUM DIKIRIM';
            $color = 'gray';
        } elseif ($outstandingQty <= 0) {
            $status = 'SELESAI';
            $color = 'success';
        } else {
            $status = 'DIKIRIM SEBAGIAN';
            $color = 'warning';
        }

        $percentage = $orderQty > 0 ? min(100, round(($deliveredQty / $orderQty) * 100, 1)) : 0;

        return [
            'status'          => $status,
            'color'           => $color,
            'order_qty'       => $orderQty,
            'delivered_qty'   => $deliveredQty,
            'outstanding_qty' => $outstandingQty,
            'percentage'      => $percentage,
        ];
    }
}
