<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollBoronganItem extends Model
{
    use HasFactory;

    protected $table = 'payroll_borongan_items';

    protected $fillable = [
        'payroll_borongan_id',
        'urutan',
        'karyawan_id',
        'nik_pekerja',
        'nama_pekerja',
        'tanggal_kerja',
        'jenis_pekerjaan',
        'protol_qty',
        'protol_tarif',
        'protol_gaji',
        'packing_qty',
        'packing_tarif',
        'packing_gaji',
        'lain_qty',
        'lain_tarif',
        'lain_gaji',
        'tunj_hadir',
        'tunj_makan',
        'tunj_lain',
        'potongan',
        'total',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_kerja' => 'date',
        'protol_qty'    => 'float',
        'protol_tarif'  => 'float',
        'protol_gaji'   => 'float',
        'packing_qty'   => 'float',
        'packing_tarif' => 'float',
        'packing_gaji'  => 'float',
        'lain_qty'      => 'float',
        'lain_tarif'    => 'float',
        'lain_gaji'     => 'float',
        'tunj_hadir'    => 'float',
        'tunj_makan'    => 'float',
        'tunj_lain'     => 'float',
        'potongan'      => 'float',
        'total'         => 'float',
    ];

    public function payrollBorongan()
    {
        return $this->belongsTo(PayrollBorongan::class, 'payroll_borongan_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $item) {
            if ($item->protol_qty > 0 && $item->protol_tarif > 0 && (float) $item->protol_gaji <= 0) {
                $item->protol_gaji = round((float) $item->protol_qty * (float) $item->protol_tarif, 2);
            }
            if ($item->packing_qty > 0 && $item->packing_tarif > 0 && (float) $item->packing_gaji <= 0) {
                $item->packing_gaji = round((float) $item->packing_qty * (float) $item->packing_tarif, 2);
            }
            if ($item->lain_qty > 0 && $item->lain_tarif > 0 && (float) $item->lain_gaji <= 0) {
                $item->lain_gaji = round((float) $item->lain_qty * (float) $item->lain_tarif, 2);
            }

            $gaji = (float) $item->protol_gaji + (float) $item->packing_gaji + (float) $item->lain_gaji;
            $tunj = (float) $item->tunj_hadir + (float) $item->tunj_makan + (float) $item->tunj_lain;
            $item->total = max(0.0, round($gaji + $tunj - (float) $item->potongan, 2));
        });
    }
}
