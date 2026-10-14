<?php

namespace App\Models;

use App\Services\Finance\TreasuryService;
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

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'nominal'           => 'float',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function accountBank()
    {
        return $this->belongsTo(MasterCOA::class, 'account_bank_id');
    }

    public function accountKas()
    {
        return $this->belongsTo(MasterCOA::class, 'account_kas_id');
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO GENERATE CODE
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode_transaksi) || $model->kode_transaksi === 'AUTO') {
                $model->kode_transaksi = app(TreasuryService::class)->generateKodeKas(
                    $model->tanggal_transaksi ? \Carbon\Carbon::parse($model->tanggal_transaksi) : null
                );
            }
        });
    }
}