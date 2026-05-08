<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterItem extends Model
{
    protected $table = 'master_items';

   protected $fillable = [
    'kode_master_item',
    'nama_master_item',
    'min_stock',
    'min_order',

    'qty',
    'panjang',
    'lebar',
    'tinggi',
    'berat',

    'satuan_id',
    'category_item_id',
    'type_item_id'
];

    public function satuan()
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }

    public function kategori()
    {
        return $this->belongsTo(CategoryItem::class, 'category_item_id');
    }

    public function typeItem()
    {
        return $this->belongsTo(TypeItem::class, 'type_item_id');
    }
    public function salesOrders()
{
    return $this->morphMany(SalesOrder::class, 'itemable');
}
public function stock()
{
    return $this->hasOne(MaterialStock::class, 'item_id');
}
}