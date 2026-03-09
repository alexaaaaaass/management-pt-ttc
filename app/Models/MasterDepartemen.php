<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Karyawan;

class MasterDepartemen extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama_departemen',
        'deskripsi',
        'status',
    ];

    // Relasi ke Karyawan
    public function karyawans()
    {
        return $this->hasMany(Karyawan::class, 'departemen_id');
    }
}