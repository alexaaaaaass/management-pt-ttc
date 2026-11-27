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
        'gaji_id',
        'tahap_cicilan',
        'tanggal_pembayaran',
        'nominal_pembayaran',
        'keterangan',
        'account_kas_id',
        'post_to_ledger',
    ];

    protected $casts = [
        'tanggal_pembayaran' => 'date',
        'nominal_pembayaran' => 'float',
        'post_to_ledger'     => 'boolean',
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
            if (empty($pembayaran->no_bukti) || strtoupper(trim($pembayaran->no_bukti)) === 'AUTO') {
                $prefix = 'BYR/' . now()->format('Y') . '/' . now()->format('m') . '/';
                $seq = 1;
                $last = self::where('no_bukti', 'like', "{$prefix}%")->orderByDesc('id')->value('no_bukti');
                if ($last && preg_match('/\/(\d+)$/', $last, $m)) {
                    $seq = ((int) $m[1]) + 1;
                }
                while (self::where('no_bukti', $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT))->exists()) {
                    $seq++;
                }
                $pembayaran->no_bukti = $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function gaji()
    {
        return $this->belongsTo(Gaji::class, 'gaji_id');
    }

    public function pengPinjaman()
    {
        return $this->belongsTo(PengPinjaman::class, 'peng_pinjaman_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function accountKas()
    {
        return $this->belongsTo(MasterCOA::class, 'account_kas_id');
    }
}