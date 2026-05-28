<?php

namespace App\Models;

use App\Models\TransFakturItem;
use Illuminate\Database\Eloquent\Model;

class TransFaktur extends Model
{
   protected $fillable = [

    'purchase_order_id',

    'pic_id',

    'gudang',

    'no_invoice',
    'no_faktur',

    'tanggal_transaksi',

    'subtotal',
    'ppn',
    'grand_total',
];

    public function purchaseOrder()
    {
        return $this->belongsTo(
            PurchaseOrder::class
        );
    }
    public function pic()
{
    return $this->belongsTo(
        Karyawan::class,
        'pic_id'
    );
}

    public function items()
    {
        return $this->hasMany(
            TransFakturItem::class
        );
    }
}