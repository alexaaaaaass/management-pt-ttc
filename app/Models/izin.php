<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Izin extends Model
{
    use HasFactory;

    protected $table = 'izins';

    protected $fillable = [
        'karyawan_id',
        'tanggal_izin',
        'jenis_izin',
        'jam_mulai',
        'jam_selesai',
        'keterangan',
    ];

    // 🔥 relasi ke karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}