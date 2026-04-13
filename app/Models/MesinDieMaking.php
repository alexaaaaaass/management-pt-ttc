<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MesinDieMaking extends Model
{
    protected $table = 'mesin_die_makings';

    protected $fillable = [
        'nama_mesin',
        'jenis_mesin',
        'kapasitas',
        'proses',
        'status',
    ];
}