<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterSatuan extends Model
{
    protected $table = 'master_satuans';

    protected $fillable = [
        'kode_satuan',
        'nama_satuan'
    ];

    // relasi ke master item
    public function items()
    {
        return $this->hasMany(MasterItem::class, 'satuan_id');
    }

    // relasi sebagai satuan pertama
    public function konversiSatuanSatu()
    {
        return $this->hasMany(MasterKonversi::class, 'satuan_satu_id');
    }

    // relasi sebagai satuan kedua
    public function konversiSatuanDua()
    {
        return $this->hasMany(MasterKonversi::class, 'satuan_dua_id');
    }
}