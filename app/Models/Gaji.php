<?php

namespace App\Models;

use App\Services\HR\PayrollService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gaji extends Model
{
    use HasFactory;

    protected $table = 'gajis';

    protected $fillable = [
        'kode_slip',
        'karyawan_id',
        'periode',
        'tanggal_pembayaran',
        'metode_pembayaran',
        'account_kas_id',
        'gaji_pokok',
        'tunjangan_jabatan',
        'tunjangan_kompetensi',
        'tunjangan_intensif',
        'total_lembur',
        'total_bonus',
        'total_pendapatan',
        'potongan_tunjangan',
        'potongan_kehadiran',
        'potongan_pinjaman',
        'potongan_bpjs',
        'total_potongan',
        'gaji_bersih',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_pembayaran'   => 'date',
        'gaji_pokok'           => 'float',
        'tunjangan_jabatan'    => 'float',
        'tunjangan_kompetensi' => 'float',
        'tunjangan_intensif'   => 'float',
        'total_lembur'         => 'float',
        'total_bonus'          => 'float',
        'total_pendapatan'     => 'float',
        'potongan_tunjangan'   => 'float',
        'potongan_kehadiran'   => 'float',
        'potongan_pinjaman'    => 'float',
        'potongan_bpjs'        => 'float',
        'total_potongan'       => 'float',
        'gaji_bersih'          => 'float',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function accountKas()
    {
        return $this->belongsTo(MasterCOA::class, 'account_kas_id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode_slip) || $model->kode_slip === 'AUTO') {
                $model->kode_slip = app(PayrollService::class)->generateKodeSlip(
                    $model->tanggal_pembayaran ? \Carbon\Carbon::parse($model->tanggal_pembayaran) : null
                );
            }
        });
    }
}
