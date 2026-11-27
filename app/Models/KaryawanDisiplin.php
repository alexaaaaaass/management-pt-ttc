<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KaryawanDisiplin extends Model
{
    protected $table = 'karyawan_disiplins';

    public const JENIS_OPTIONS = [
        'TEGURAN'  => 'Teguran Lisan / Tertulis',
        'SP1'      => 'Surat Peringatan 1 (SP1)',
        'SP2'      => 'Surat Peringatan 2 (SP2)',
        'SP3'      => 'Surat Peringatan 3 (SP3)',
        'SCORSING' => 'Scorsing / Skorsing (Potong Gaji)',
    ];

    public const STATUS_OPTIONS = [
        'Aktif'   => 'Aktif (Berlaku)',
        'Selesai' => 'Selesai / Masa Berlaku Habis',
        'Dicabut' => 'Dicabut',
    ];

    protected $fillable = [
        'karyawan_id',
        'nomor_surat',
        'jenis',
        'tanggal_surat',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari_scorsing',
        'pelanggaran',
        'tindakan',
        'lampiran',
        'status',
        'diterbitkan_oleh',
    ];

    protected $casts = [
        'tanggal_surat'        => 'date',
        'tanggal_mulai'        => 'date',
        'tanggal_selesai'      => 'date',
        'jumlah_hari_scorsing' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->nomor_surat) || strtoupper(trim($model->nomor_surat)) === 'AUTO') {
                $prefix = ($model->jenis ?: 'SP') . '/HRD/' . now()->format('Ym') . '/';
                $seq = self::where('nomor_surat', 'like', "{$prefix}%")->count() + 1;
                while (self::where('nomor_surat', $prefix . str_pad((string) $seq, 3, '0', STR_PAD_LEFT))->exists()) {
                    $seq++;
                }
                $model->nomor_surat = $prefix . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            }
            if (empty($model->diterbitkan_oleh)) {
                $model->diterbitkan_oleh = auth()->user()?->name ?? 'HRD PT TTC';
            }
        });
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
}
