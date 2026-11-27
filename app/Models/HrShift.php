<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrShift extends Model
{
    protected $table = 'hr_shifts';

    protected $fillable = [
        'kode',
        'nama',
        'jam_masuk',
        'jam_pulang',
        'toleransi_telat_menit',
        'is_overnight',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'toleransi_telat_menit' => 'integer',
        'is_overnight'          => 'boolean',
        'is_active'             => 'boolean',
    ];

    public function karyawans()
    {
        return $this->hasMany(Karyawan::class, 'shift_id');
    }

    public function getLabelLengkapAttribute(): string
    {
        return "{$this->nama} ({$this->jam_masuk}–{$this->jam_pulang})";
    }
}
