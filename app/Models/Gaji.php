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
        'tipe_payroll',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari_hadir',
        'jumlah_alpha',
        'jumlah_terlambat',
        'tanggal_pembayaran',
        'metode_pembayaran',
        'account_kas_id',
        'coa_beban_id',
        'npb_request_id',
        'gaji_pokok',
        'tunjangan_jabatan',
        'tunjangan_kompetensi',
        'tunjangan_intensif',
        'tunjangan_makan',
        'tunjangan_hadir',
        'tunjangan_lain',
        'total_lembur',
        'total_bonus',
        'total_pendapatan',
        'potongan_tunjangan',
        'potongan_kehadiran',
        'potongan_pinjaman',
        'potongan_bpjs',
        'bpjs_perusahaan',
        'potongan_pph21',
        'potongan_lain',
        'total_potongan',
        'gaji_bersih',
        'status',
        'is_pinjaman_synced',
        'post_to_ledger',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_pembayaran'   => 'date',
        'tanggal_mulai'        => 'date',
        'tanggal_selesai'      => 'date',
        'jumlah_hari_hadir'    => 'float',
        'jumlah_alpha'         => 'integer',
        'jumlah_terlambat'     => 'integer',
        'gaji_pokok'           => 'float',
        'tunjangan_jabatan'    => 'float',
        'tunjangan_kompetensi' => 'float',
        'tunjangan_intensif'   => 'float',
        'tunjangan_makan'      => 'float',
        'tunjangan_hadir'      => 'float',
        'tunjangan_lain'       => 'float',
        'total_lembur'         => 'float',
        'total_bonus'          => 'float',
        'total_pendapatan'     => 'float',
        'potongan_tunjangan'   => 'float',
        'potongan_kehadiran'   => 'float',
        'potongan_pinjaman'    => 'float',
        'potongan_bpjs'        => 'float',
        'bpjs_perusahaan'      => 'float',
        'potongan_pph21'       => 'float',
        'potongan_lain'        => 'float',
        'total_potongan'       => 'float',
        'gaji_bersih'          => 'float',
        'is_pinjaman_synced'   => 'boolean',
        'post_to_ledger'       => 'boolean',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function accountKas()
    {
        return $this->belongsTo(MasterCOA::class, 'account_kas_id');
    }

    public function coaBeban()
    {
        return $this->belongsTo(MasterCOA::class, 'coa_beban_id');
    }

    public function npbRequest()
    {
        return $this->belongsTo(NpbRequest::class, 'npb_request_id');
    }

    public function getIsLedgerPostedAttribute(): bool
    {
        return CoaMovement::where('reference_type', 'PAYROLL')
            ->where('reference_id', $this->id)
            ->exists();
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
