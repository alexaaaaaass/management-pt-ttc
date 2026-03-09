<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterFinishing extends Model
{
    protected $table = 'master_finishings';

    protected $fillable = [
        'nama_mesin',
        'jenis_mesin',
        'kapasitas',
        'proses',
        'status'
    ];
}