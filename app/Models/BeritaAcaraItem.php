<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeritaAcaraItem extends Model
{
    protected $fillable = [
        'item_product',
        'qty_kedatangan',
        'qty_penerimaan',
        'qty_penolakan',
        'catatan_penerimaan',
        'foto_referensi',
        'foto_fisik',
        'urutan',
    ];

    protected $casts = [
        'qty_kedatangan' => 'decimal:2',
        'qty_penerimaan' => 'decimal:2',
        'qty_penolakan' => 'decimal:2',
    ];

    public function beritaAcara()
    {
        return $this->belongsTo(BeritaAcara::class);
    }
}