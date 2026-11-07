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
        static::saving(function ($pembayaran) {
            if ($pembayaran->peng_pinjaman_id && $pembayaran->nominal_pembayaran > 0) {
                $pinjaman = PengPinjaman::find($pembayaran->peng_pinjaman_id);
                if ($pinjaman) {
                    $val = (float) $pembayaran->nominal_pembayaran;
                    $cicilan = (float) $pinjaman->cicilan_per_bulan;
                    $sudahBayar = (float) $pinjaman->pembayaran()
                        ->when($pembayaran->exists, fn ($q) => $q->where('id', '!=', $pembayaran->id))
                        ->sum('nominal_pembayaran');
                    $sisa = max(0.0, (float) $pinjaman->nilai_pinjaman - $sudahBayar);

                    if ($sisa > 0 && abs($sisa - $val) > 0 && abs($sisa - $val) <= 1000) {
                        $pembayaran->nominal_pembayaran = $sisa;
                    } elseif ($cicilan > 0 && abs($cicilan - $val) > 0 && abs($cicilan - $val) <= 1000) {
                        $pembayaran->nominal_pembayaran = $cicilan;
                    }
                }
            }
        });

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