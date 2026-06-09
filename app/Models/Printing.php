<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ErrorProduction;
class Printing extends Model
{
    protected $table = 'printings';

  protected $fillable = [
    'spk_id',
    'mesin_id',
    'operator_id',
    'tanggal_entri',
    'proses_printing',
    'tahap_printing',
    'hasil_baik',
    'hasil_rusak',
    'semi_waste',
    'error_production_id',
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
        return $this->belongsTo(MasterPrinting::class, 'mesin_id');
    }

    public function operator()
    {
        return $this->belongsTo(MasterOperator::class, 'operator_id');
    }
    

    public function errorProduction()
    {
        return $this->belongsTo(ErrorProduction::class);
    }
}