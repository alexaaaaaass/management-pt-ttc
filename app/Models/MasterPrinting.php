<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterPrinting extends Model
{
    protected $table = 'master_printings';

    protected $fillable = [
        'nama_mesin',
        'jenis_mesin',
        'kapasitas',
        'proses',
        'status'
    ];
}