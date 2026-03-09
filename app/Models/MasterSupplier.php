<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterSupplier extends Model
{
    protected $table = 'master_suppliers';

    protected $fillable = [
        'kode_supplier',
        'nama_supplier',
        'jenis_supplier',
        'alamat',
        'keterangan'
    ];
}