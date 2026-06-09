<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembPinjaman extends Model
{
    use HasFactory;

    protected $table = 'pemb_pinjamans';

    protected $fillable = [
        'no_bukti',
        'peng_pinjaman_id',
        'karyawan_id',
        'tahap_cicilan',
        'tanggal_pembayaran',
        'nominal_pembayaran',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_pembayaran' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function ($pembayaran) {

            $count = self::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count() + 1;

            $pembayaran->no_bukti =
                'BYR/' .
                now()->format('Y') . '/' .
                now()->format('m') . '/' .
                str_pad($count, 4, '0', STR_PAD_LEFT);
        });
    }

    public function pengPinjaman()
    {
        return $this->belongsTo(PengPinjaman::class, 'peng_pinjaman_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}