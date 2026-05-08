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
    ];
}