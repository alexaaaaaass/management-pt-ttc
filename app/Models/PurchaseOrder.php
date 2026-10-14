<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PurchaseOrder extends Model
{
      protected $fillable = [
        'no_po',
        'po_type', // tambahkan ini
        'purchase_request_id',
        'supplier_id',
        'po_date',
        'eta',
        'currency',
        'ppn',
        'ongkir',
        'dp',
        'status',
    ];
    
      public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function supplier()
    {
        return $this->belongsTo(MasterSupplier::class,'supplier_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
    public function fakturs()
    {
        return $this->hasMany(TransFaktur::class);
    }

    public function penerimaanBarangs()
    {
        return $this->hasMany(PenerimaanBarang::class, 'purchase_order_id');
    }

    public function poBillings()
    {
        return $this->hasMany(PoBilling::class, 'purchase_order_id');
    }

    public function getReceivingProgressAttribute(): array
    {
        return app(\App\Services\PurchaseOrder\PurchaseOrderService::class)->getReceivingProgress($this);
    }

    protected static function booted()
    {
        static::creating(function ($po) {
            if (empty($po->no_po)) {
                $po->no_po = app(\App\Services\PurchaseOrder\PurchaseOrderService::class)->generateNoPo($po->po_date);
            }
        });

        static::deleting(function ($po) {
            if ($po->poBillings()->exists()) {
                throw new \Exception('Purchase Order tidak dapat dihapus karena sudah memiliki Tagihan Supplier (PO Billing) terkait.');
            }

            if ($po->penerimaanBarangs()->exists()) {
                throw new \Exception('Purchase Order tidak dapat dihapus karena sudah memiliki Penerimaan Barang terkait.');
            }

            if ($po->fakturs()->exists()) {
                throw new \Exception('Purchase Order tidak dapat dihapus karena sudah memiliki Faktur terkait.');
            }
        });
    }
}