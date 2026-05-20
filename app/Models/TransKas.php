<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransKas extends Model
{
    use HasFactory;

    protected $table = 'trans_kas';

    protected $fillable = [

        'kode_transaksi',

        'tipe_transaksi',

        'gudang',

        'tanggal_transaksi',

        'periode',

        'karyawan_id',

        'account_bank_id',

        'account_kas_id',

        'customer_id',

        'nominal',

        'status',

        'keterangan',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function karyawan()
    {
        return $this->belongsTo(
            Karyawan::class
        );
    }

    public function accountBank()
    {
        return $this->belongsTo(
            MasterCOA::class,
            'account_bank_id'
        );
    }

    public function accountKas()
    {
        return $this->belongsTo(
            MasterCOA::class,
            'account_kas_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO GENERATE CODE
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($model) {

            $count =
                self::count() + 1;

            $model->kode_transaksi =
                'TRK-'
                . now()->format('Ym')
                . '-'
                . str_pad(
                    $count,
                    4,
                    '0',
                    STR_PAD_LEFT
                );
        });
    }
}