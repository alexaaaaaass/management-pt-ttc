<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DieMaking extends Model
{
    protected $table = 'diemakings';

    protected $fillable = [
        'spk_id',
        'mesin_id',
        'operator_id',
        'tanggal_entri',
        'proses_diemaking',
        'tahap_diemaking',
        'hasil_baik',
        'hasil_rusak',
        'semi_waste',
        'note_waste',
        'keterangan_spk',
    ];

    protected $casts = [
        'tanggal_entri' => 'date',
    ];

    public function spk()
    {
        return $this->belongsTo(MasterSpk::class, 'spk_id');
    }

    public function mesin()
    {
        return $this->belongsTo(MesinDieMaking::class, 'mesin_id');
    }

    public function operator()
    {
        return $this->belongsTo(OperatorDieMaking::class, 'operator_id');
    }
}