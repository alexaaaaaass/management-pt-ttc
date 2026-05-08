<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
   protected $fillable = [
    'purchase_order_id',
    'item_id',

    'qty_pr',
    'qty_po',
    'qty_konversi',

    'price',
    'discount',
    'total',

    'satuan',
    'eta',
    'catatan'
];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(MasterItem::class,'item_id');
    }
}