<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MesinPraCetak extends Model
{
    protected $table = 'mesin_pra_cetaks';

    protected $fillable = [
        'nama_mesin',
        'jenis_mesin',
        'kapasitas',
        'proses',
        'status',
        'tarif_per_plate',
        'tarif_per_jam',
    ];

    protected $casts = [
        'tarif_per_plate' => 'decimal:2',
        'tarif_per_jam'   => 'decimal:2',
    ];
}
