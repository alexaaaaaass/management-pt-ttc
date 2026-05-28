<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransFakturItem extends Model
{
    protected $fillable = [

        'trans_faktur_id',
        'deskripsi',
        'qty',
        'unit',
        'harga_satuan',
        'diskon',
        'total',
        'catatan',
    ];

    public function transFaktur()
    {
        return $this->belongsTo(
            TransFaktur::class
        );
    }
}