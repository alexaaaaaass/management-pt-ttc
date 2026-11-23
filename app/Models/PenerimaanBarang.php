<?php

namespace App\Models;

use App\Models\PenerimaanBarangItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;


class PenerimaanBarang extends Model
{
    protected $table = 'penerimaan_barangs';

    protected $fillable = [

    'no_penerimaan',

    'purchase_order_id',

    'tanggal_terima',

    'no_surat_jalan',

    'nama_pengirim',

    'nopol_kendaraan',

    'catatan_pengiriman',

];
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items()
    {
        return $this->hasMany(PenerimaanBarangItem::class);
    }

    public function externalReturns()
    {
        return $this->hasMany(
            ExternalReturn::class
        );
    }

    public function poBilling()
    {
        return $this->hasOne(PoBilling::class);
    }

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($pb) {
            if (empty($pb->no_penerimaan)) {
                $pb->no_penerimaan = app(\App\Services\PurchaseOrder\PenerimaanBarangService::class)->generateNoPenerimaan();
            }
        });

        static::deleting(function (PenerimaanBarang $pb) {
            if ($pb->poBilling()->exists()) {
                throw new \Exception('Penerimaan Barang tidak dapat dihapus karena sudah memiliki Tagihan Supplier (PO Billing) terkait.');
            }

            if ($pb->externalReturns()->exists()) {
                throw new \Exception('Penerimaan Barang tidak dapat dihapus karena sudah memiliki Retur Eksternal terkait.');
            }

            $pb->loadMissing(['items.purchaseOrderItem', 'purchaseOrder']);

            foreach ($pb->items as $item) {
                $poItem = $item->purchaseOrderItem;
                if ($poItem && (float) $item->qty_terima > 0) {
                    $pb->applyStockMovement(
                        poItem: $poItem,
                        qty: (float) $item->qty_terima,
                        isIncrease: false,
                        remarks: 'Rollback Hapus Penerimaan Barang - ' . $pb->no_penerimaan
                    );
                    $poItem->decrement('qty_received', $item->qty_terima);
                }
            }

            if ($pb->purchaseOrder) {
                app(\App\Services\PurchaseOrder\PurchaseOrderService::class)->syncReceivingStatus($pb->purchaseOrder);
            }
        });
    }

    /**
     * Auto-routing stok dari LPB:
     * - Jika item terhubung ke MasterMaterialPlate -> masuk/rollback ke Gudang Plate Prepress (PracetakMaterialStock).
     * - Selain itu -> masuk/rollback ke Gudang Umum (MaterialStock).
     */
    public function applyStockMovement(
        PurchaseOrderItem $poItem,
        float $qty,
        bool $isIncrease,
        string $remarks
    ): void {
        if ($qty <= 0) {
            return;
        }

        $plate = MasterMaterialPlate::where('master_item_id', $poItem->item_id)->first();

        if ($plate) {
            $pracetakStock = app(\App\Services\Pracetak\PracetakStockService::class);
            $unitPrice = (float) ($poItem->price ?? 0) > 0
                ? (float) $poItem->price
                : (float) $plate->harga_beli;
            $supplierId = $this->purchaseOrder?->supplier_id ?: $plate->supplier_id;
            $noRef = $this->no_penerimaan ?: ($this->no_surat_jalan ?: ('LPB-' . $this->id));

            if ($isIncrease) {
                $pracetakStock->receiveFromLpb(
                    plate: $plate,
                    reference: $this,
                    qty: $qty,
                    unitPrice: $unitPrice,
                    supplierId: $supplierId,
                    userId: auth()->id(),
                    nomorTransaksi: $noRef,
                    tanggal: $this->tanggal_terima ? Carbon::parse($this->tanggal_terima)->toDateString() : now()->toDateString(),
                    catatan: $remarks
                );
            } else {
                $pracetakStock->rollbackFromLpb(
                    plate: $plate,
                    reference: $this,
                    qty: $qty,
                    unitPrice: $unitPrice,
                    supplierId: $supplierId,
                    userId: auth()->id(),
                    nomorTransaksi: 'REV-' . $noRef,
                    catatan: $remarks
                );
            }

            return;
        }

        $stockService = app(\App\Services\Inventory\StockService::class);
        if ($isIncrease) {
            $stockService->increaseStock(
                itemId: $poItem->item_id,
                qty: $qty,
                reference: $this,
                remarks: $remarks
            );
        } else {
            $stockService->decreaseStock(
                itemId: $poItem->item_id,
                qty: $qty,
                reference: $this,
                remarks: $remarks
            );
        }
    }

    public function processReceiving(): void
    {
        $this->loadMissing(['items.purchaseOrderItem', 'purchaseOrder']);

        foreach ($this->items as $item) {
            $poItem = $item->purchaseOrderItem;

            if (! $poItem || (float) $item->qty_terima <= 0) {
                continue;
            }

            $this->applyStockMovement(
                poItem: $poItem,
                qty: (float) $item->qty_terima,
                isIncrease: true,
                remarks: 'Penerimaan Barang PO - ' . ($this->no_penerimaan ?: $this->no_surat_jalan)
            );

            $poItem->increment('qty_received', $item->qty_terima);
        }

        if ($this->purchaseOrder) {
            app(\App\Services\PurchaseOrder\PurchaseOrderService::class)->syncReceivingStatus($this->purchaseOrder);
        }
    }
}