<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpbFundPosition extends Model
{
    use HasFactory;

    protected $table = 'npb_fund_positions';

    protected $fillable = [
        'npb_request_id',
        'urutan',
        'coa_id',
        'nama_rekening',
        'saldo',
        'keterangan',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'saldo'  => 'float',
    ];

    public function npbRequest()
    {
        return $this->belongsTo(NpbRequest::class, 'npb_request_id');
    }

    public function coa()
    {
        return $this->belongsTo(MasterCOA::class, 'coa_id');
    }
}
