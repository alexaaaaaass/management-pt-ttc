<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MesinFinis extends Model
{
     protected $table = 'mesin_finishing';

    protected $fillable = [
        'nama_mesin',
        'jenis_mesin',
        'kapasitas',
        'proses',
        'status'
    ];
}