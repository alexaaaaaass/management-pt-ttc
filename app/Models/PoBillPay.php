<?php

namespace App\Models;

use App\Models\PoBillPayItem;
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

    public function poBilling()
    {
        return $this->belongsTo(
            PoBilling::class
        );
    }

    public function karyawan()
    {
        return $this->belongsTo(
            Karyawan::class
        );
    }

    public function items()
    {
        return $this->hasMany(
            PoBillPayItem::class
        );
    }

    protected static function booted()
    {
        static::creating(function ($data) {

            $date = Carbon::now()
                ->format('Ymd');

            $count = self::count() + 1;

            $urut = str_pad(
                $count,
                3,
                '0',
                STR_PAD_LEFT
            );

            $data->kode_pembayaran =
                "PAY-{$date}-{$urut}";
        });
    }
}