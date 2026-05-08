<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterFinishing extends Model
{
    protected $table = 'master_finishing';

    protected $fillable = [
        'spk_id',
        'mesin_finishing_id',
        'operator_finishing_id',
        'tanggal_entri',
        'proses_finishing',
        'tahap_finishing',
        'hasil_baik',
        'hasil_rusak',
        'semi_waste',
        'note_waste',
        'keterangan_spk',
        'keterangan',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function spk()
    {
        return $this->belongsTo(MasterSpk::class, 'spk_id');
    }

    public function mesinFinishing()
    {
        return $this->belongsTo(MesinFinis::class, 'mesin_finishing_id');
    }

    public function operatorFinishing()
    {
        return $this->belongsTo(OperatorFinishing::class, 'operator_finishing_id');
    }
}