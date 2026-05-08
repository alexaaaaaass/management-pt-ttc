<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Absensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'karyawan_id',
        'nip',
        'pin',
        'jabatan',
        'departemen',
        'kantor',
        'tanggal_scan',
        'sn',
        'mesin',
        'workcode',
        'io',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}