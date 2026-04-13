<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenerimaanBarangItem extends Model
{
    protected $table = 'penerimaan_barang_items';

    protected $fillable = [
        'penerimaan_barang_id',
        'purchase_order_item_id',
        'qty_terima',
        'tgl_exp',
        'no_lot',
        'catatan_item',
    ];

    public function penerimaanBarang()
    {
        return $this->belongsTo(PenerimaanBarang::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }
}