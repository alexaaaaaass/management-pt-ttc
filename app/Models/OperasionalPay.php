<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperasionalPay extends Model
{
    use HasFactory;

    protected $fillable = [

        'kode_transaksi',
        'gudang',
        'periode',
        'tanggal_transaksi',

        'account_kas_id',
        'account_beban_id',

        'karyawan_id',

        'nominal',

        'nopol',
        'odometer',
        'mesin',
        'jenis',
        'kode',

        'keterangan',
        'status',
    ];

    protected static function booted()
    {
        static::creating(function ($record) {

            $lastId = static::max('id') + 1;

            $record->kode_transaksi =
                'OPR-' .
                str_pad($lastId, 5, '0', STR_PAD_LEFT);
        });
    }

    public function accountKas()
    {
        return $this->belongsTo(
            MasterCOA::class,
            'account_kas_id'
        );
    }

    public function accountBeban()
    {
        return $this->belongsTo(
            MasterCOA::class,
            'account_beban_id'
        );
    }

    public function karyawan()
    {
        return $this->belongsTo(
            Karyawan::class
        );
    }
}