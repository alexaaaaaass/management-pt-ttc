<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PembPinjaman;

class PengPinjaman extends Model
{
    protected $table = 'peng_pinjamans';

    protected $fillable = [
        'nomor_bukti',
        'karyawan_id',
        'kode_gudang',
        'tanggal_pengajuan',
        'nilai_pinjaman',
        'jangka_waktu',
        'cicilan_per_bulan',
        'keperluan_pinjaman',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function ($pengPinjaman) {

            $count = self::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count() + 1;

            $pengPinjaman->nomor_bukti =
                'PJM/' .
                now()->format('Y') . '/' .
                now()->format('m') . '/' .
                str_pad($count, 4, '0', STR_PAD_LEFT);
        });
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function pembayaran()
{
    return $this->hasMany(PembPinjaman::class, 'peng_pinjaman_id');
}

}