<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PotTunjangan extends Model
{
    use HasFactory;

    protected $table = 'pot_tunjangans';

    protected $fillable = [
        'karyawan_id',
        'periode_payroll',
        'potongan_jabatan',
        'potongan_kompetensi',
        'potongan_intensif',
        'total_potongan',
        'keterangan',
    ];

    protected $casts = [
        'potongan_jabatan'    => 'float',
        'potongan_kompetensi' => 'float',
        'potongan_intensif'   => 'float',
        'total_potongan'      => 'float',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}