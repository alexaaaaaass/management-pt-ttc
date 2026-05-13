<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SuratJalan extends Model
{
    use HasFactory;

    protected $fillable = [

        'kode_surat_jalan',

        'sales_order_id',
        'spk_id',

        'qty_pengiriman',

        'tanggal_surat_jalan',

        'alamat_tujuan',

        'transportasi',
        'no_polisi',

        'driver',
        'pengirim',

        'keterangan',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function spk()
    {
        return $this->belongsTo(MasterSpk::class, 'spk_id');
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO GENERATE NOMOR SJ
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($model) {

            $tanggal = Carbon::now()->format('Ymd');

            $count = self::whereDate('created_at', today())->count() + 1;

            $model->kode_surat_jalan =
                'SJ-' .
                $tanggal .
                '-' .
                str_pad($count, 3, '0', STR_PAD_LEFT);
        });
    }
}