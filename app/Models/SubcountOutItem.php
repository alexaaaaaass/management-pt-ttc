<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubcountOutItem extends Model
{
    protected $fillable = [

        'subcount_out_id',

        'sumber_item',

        'spk_id',

        'item_id',

        'qty',

        'satuan_id',

        'nama_produk',

        'catatan',
    ];

    public function subcountOut()
    {
        return $this->belongsTo(SubcountOut::class);
    }

    public function spk()
    {
        return $this->belongsTo(MasterSpk::class,'spk_id');
    }

    public function item()
    {
        return $this->belongsTo(MasterItem::class,'item_id');
    }

    public function satuan()
    {
        return $this->belongsTo(MasterSatuan::class);
    }
}