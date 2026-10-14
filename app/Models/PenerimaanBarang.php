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
            $stockService = app(\App\Services\Inventory\StockService::class);

            foreach ($pb->items as $item) {
                $poItem = $item->purchaseOrderItem;
                if ($poItem && (float) $item->qty_terima > 0) {
                    $stockService->decreaseStock(
                        itemId: $poItem->item_id,
                        qty: (float) $item->qty_terima,
                        reference: $pb,
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

    public function processReceiving(): void
    {
        $this->loadMissing('items.purchaseOrderItem');
        $stockService = app(\App\Services\Inventory\StockService::class);

        foreach ($this->items as $item) {
            $poItem = $item->purchaseOrderItem;

            if (! $poItem) {
                continue;
            }

            $stockService->increaseStock(
                itemId: $poItem->item_id,
                qty: (float) $item->qty_terima,
                reference: $this,
                remarks: 'Penerimaan Barang PO - ' . ($this->no_penerimaan ?: $this->no_surat_jalan)
            );

            $poItem->increment('qty_received', $item->qty_terima);
        }

        if ($this->purchaseOrder) {
            app(\App\Services\PurchaseOrder\PurchaseOrderService::class)->syncReceivingStatus($this->purchaseOrder);
        }
    }
}