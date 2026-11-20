<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PracetakProcess extends Model
{
    protected $table = 'pracetak_processes';

    protected $fillable = [
        'spk_id',
        'qty',
        'status',
        'ket_spk',
        'nama_plat',
        'hasil_baik',
        'hasil_rusak',
        'tgl_entry',
        'operator_id',
        'mesin_id',
        // A. Trial Design
        'status_file',
        'status_kesiapan_file',
        'toleransi_hasil',
        'catatan_design',
        // B. Prove Print
        'is_prove_print',
        'bahan_prove_print',
        'status_prove_print',
        'arsip_proses_prove',
        // C. Plate Making
        'jumlah_warna_plate',
        'detail_warna_plate',
        'kerusakan_plate',
        'catatan_kerusakan_plate',
        // D. Penyimpanan Material & Finishing
        'lokasi_simpan_plate',
        'material_pendukung',
        'toleransi_rusak_hilang',
    ];

    // 🔥 RELASI
    public function spk()
    {
        return $this->belongsTo(MasterSpk::class, 'spk_id');
    }

    public function operator()
    {
        return $this->belongsTo(OperatorPraCetak::class, 'operator_id');
    }

    public function mesin()
    {
        return $this->belongsTo(MesinPraCetak::class, 'mesin_id');
    }

    protected $attributes = [
    'status' => 'plat_making',
];
}