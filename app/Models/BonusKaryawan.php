<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusKaryawan extends Model
{
    use HasFactory;

    protected $table = 'bonus_karyawans';

    protected $fillable = [
        'kode_gudang',
        'karyawan_id',
        'tanggal_bonus',
        'periode',
        'nilai_bonus',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_bonus' => 'date',
        'nilai_bonus'   => 'float',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}