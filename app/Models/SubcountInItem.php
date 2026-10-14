<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubcountInItem extends Model
{
    protected $fillable = [
        'subcount_in_id',
        'subcount_out_id',
        'item_asal_id',
        'item_hasil_id',
        'qty_dikirim',
        'qty_diterima',
        'keterangan',
    ];

    public function subcountIn()
    {
        return $this->belongsTo(SubcountIn::class);
    }

    public function subcountOut()
    {
        return $this->belongsTo(SubcountOut::class);
    }

    public function itemAsal()
    {
        return $this->belongsTo(
            MasterItem::class,
            'item_asal_id'
        );
    }

    public function itemHasil()
    {
        return $this->belongsTo(
            MasterItem::class,
            'item_hasil_id'
        );
    }
}