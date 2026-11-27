<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Izin extends Model
{
    use HasFactory;

    protected $table = 'izins';

    public const JENIS_OPTIONS = [
        'terlambat'           => 'Izin Datang Terlambat',
        'meninggalkan_kantor' => 'Izin Meninggalkan Kantor',
        'pulang_awal'         => 'Izin Pulang Awal',
        'dinas_luar'          => 'Dinas Luar',
        'sakit'               => 'Sakit (Tanpa Surat Dokter)',
        'sakit_surat_dokter'  => 'Sakit (Dengan Surat Dokter)',
        'alpha'               => 'Alpha / Mangkir',
        'scorsing'            => 'Scorsing / Skorsing (Potong Gaji)',
    ];

    protected $fillable = [
        'karyawan_id',
        'tanggal_izin',
        'jenis_izin',
        'jam_mulai',
        'jam_selesai',
        'keterangan',
        'lampiran',
        'status',
        'approved_by',
        'approved_at',
        'catatan_approval',
    ];

    protected $casts = [
        'tanggal_izin' => 'date',
        'approved_at'  => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->status)) {
                $model->status = 'Pending';
            }
        });
    }

    // 🔥 relasi ke karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}