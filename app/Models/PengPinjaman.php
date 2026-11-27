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
        'status',
        'approved_by',
        'approved_at',
        'catatan_approval',
        'account_kas_id',
        'tanggal_pencairan',
        'is_disbursed',
        'post_to_ledger',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'tanggal_pencairan' => 'date',
        'nilai_pinjaman'    => 'float',
        'cicilan_per_bulan' => 'float',
        'approved_at'       => 'datetime',
        'is_disbursed'      => 'boolean',
        'post_to_ledger'    => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($pengPinjaman) {
            if (empty($pengPinjaman->nomor_bukti) || strtoupper(trim($pengPinjaman->nomor_bukti)) === 'AUTO') {
                $prefix = 'PJM/' . now()->format('Y') . '/' . now()->format('m') . '/';
                $seq = 1;
                $last = self::where('nomor_bukti', 'like', "{$prefix}%")->orderByDesc('id')->value('nomor_bukti');
                if ($last && preg_match('/\/(\d+)$/', $last, $m)) {
                    $seq = ((int) $m[1]) + 1;
                }
                while (self::where('nomor_bukti', $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT))->exists()) {
                    $seq++;
                }
                $pengPinjaman->nomor_bukti = $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            }
            if (empty($pengPinjaman->status)) {
                $pengPinjaman->status = 'Pending';
            }
        });
    }

    public function getTotalTerbayarAttribute(): float
    {
        return round((float) $this->pembayaran()->sum('nominal_pembayaran'), 2);
    }

    public function getSisaPinjamanAttribute(): float
    {
        return max(0.0, round((float) $this->nilai_pinjaman - $this->total_terbayar, 2));
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function pembayaran()
    {
        return $this->hasMany(PembPinjaman::class, 'peng_pinjaman_id');
    }

    public function accountKas()
    {
        return $this->belongsTo(MasterCOA::class, 'account_kas_id');
    }

}