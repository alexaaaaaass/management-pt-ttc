<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PotTunjangan extends Model
{
    use HasFactory;

    protected $table = 'pot_tunjangan';

    protected $fillable = [
        'karyawan_id',
        'periode_payroll',
        'potongan_jabatan',
        'potongan_kompetensi',
        'potongan_intensif',
        'keterangan',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}