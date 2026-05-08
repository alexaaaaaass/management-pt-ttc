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
}