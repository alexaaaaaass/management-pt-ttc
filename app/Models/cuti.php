<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuti extends Model
{
    protected $fillable = [
        'karyawan_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
        'jenis_cuti',
        'lampiran',
        'keterangan',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}