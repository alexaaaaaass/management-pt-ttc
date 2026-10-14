<?php

namespace App\Models;

use App\Services\Finance\PoBillPayService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PoBillPay extends Model
{
    protected $fillable = [
        'kode_pembayaran',
        'po_billing_id',
        'karyawan_id',
        'tanggal_pembayaran',
        'gudang',
        'periode',
        'total_tagihan',
        'total_pembayaran',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_pembayaran' => 'date',
        'total_tagihan'      => 'float',
        'total_pembayaran'   => 'float',
    ];

    public function poBilling()
    {
        return $this->belongsTo(PoBilling::class);
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function items()
    {
        return $this->hasMany(PoBillPayItem::class);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode_pembayaran) || $model->kode_pembayaran === 'AUTO') {
                $model->kode_pembayaran = app(PoBillPayService::class)->generateKodePembayaran();
            }
        });
    }
}