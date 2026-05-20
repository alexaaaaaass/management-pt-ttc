<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvPayment extends Model
{
    use HasFactory;

    protected $fillable = [

        'invoice_id',

        'metode_bayar_id',

        'coa_id',

        'karyawan_id',

        'tanggal_bayar',

        'nominal_bayar',

        'keterangan',

        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function metodeBayar()
    {
        return $this->belongsTo(
            MetodeBayar::class
        );
    }

    public function coa()
    {
        return $this->belongsTo(
            MasterCOA::class,
            'coa_id'
        );
    }

    public function karyawan()
    {
        return $this->belongsTo(
            Karyawan::class
        );
    }
}