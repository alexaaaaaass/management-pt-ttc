<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'no_po',
        'purchase_request_id',
        'supplier_id',
        'po_date',
        'eta',
        'currency',
        'ppn',
        'ongkir',
        'dp'
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
      protected static function booted()
    {
        static::creating(function ($po) {

            $tanggal = $po->po_date ?? now();
            $date = Carbon::parse($tanggal)->format('Ymd');

            $count = self::whereDate('po_date', $tanggal)->count() + 1;

            $sequence = str_pad($count, 3, '0', STR_PAD_LEFT);

            $po->no_po = "PO-UGRMS-{$date}-{$sequence}";
        });
    }
}