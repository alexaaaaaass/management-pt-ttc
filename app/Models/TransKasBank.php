<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransKasBank extends Model
{
    use HasFactory;

    protected $fillable = [

        'kode_transaksi',
        'tipe_transaksi',
        'gudang',
        'tanggal_transaksi',
        'periode',

        'account_bank_id',
        'account_lawan_id',

        'nominal',

        'customer_id',

        'bank',
        'atas_nama',
        'no_rekening',

        'keterangan',

        'status',
    ];

    protected static function booted()
    {
        static::creating(function ($record) {

            $lastId = static::max('id') + 1;

            $record->kode_transaksi =
                'BANK-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);
        });
    }

    public function accountBank()
    {
        return $this->belongsTo(
            MasterCOA::class,
            'account_bank_id'
        );
    }

    public function customer()
{
    return $this->belongsTo(
        Customer::class,
        'customer_id'
    );
}

    public function accountLawan()
    {
        return $this->belongsTo(
            MasterCOA::class,
            'account_lawan_id'
        );
    }
}