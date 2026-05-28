<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoBillPayItem extends Model
{
    protected $fillable = [

        'po_bill_pay_id',

        'metode_bayar_id',

        'coa_debit_id',
        'coa_kredit_id',

        'nominal_pembayaran',

        'mata_uang',

        'nama_bank',
        'no_rekening',
        'atas_nama',

        'memo',
    ];

    public function poBillPay()
    {
        return $this->belongsTo(
            PoBillPay::class
        );
    }

    public function metodeBayar()
    {
        return $this->belongsTo(
            MetodeBayar::class
        );
    }

    public function coaDebit()
    {
        return $this->belongsTo(
            MasterCOA::class,
            'coa_debit_id'
        );
    }

    public function coaKredit()
    {
        return $this->belongsTo(
            MasterCOA::class,
            'coa_kredit_id'
        );
    }
}