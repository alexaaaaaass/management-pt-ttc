<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusKaryawan extends Model
{
    use HasFactory;

    protected $table = 'bonus_karyawan';

    protected $fillable = [
        'kode_gudang',
        'karyawan_id',
        'tanggal_bonus',
        'nilai_bonus',
        'keterangan',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}