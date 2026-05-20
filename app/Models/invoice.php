<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Invoice extends Model
{
    protected $fillable = [

        'kode_invoice',

        'surat_jalan_id',

        'tanggal_invoice',
        'tanggal_jatuh_tempo',

        'subtotal',
        'diskon',
        'ppn',
        'ongkir',
        'uang_muka',

        'grand_total',
        'sisa_tagihan',

        'catatan',
    ];

    protected $casts = [

    'subtotal' => 'float',
    'diskon' => 'float',
    'ppn' => 'float',
    'ongkir' => 'float',
    'uang_muka' => 'float',
    'grand_total' => 'float',
    'sisa_tagihan' => 'float',

];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function suratJalan()
    {
        return $this->belongsTo(SuratJalan::class);
    }

    public function payments()
{
    return $this->hasMany(
        InvPayment::class
    );
}

    /*
    |--------------------------------------------------------------------------
    | AUTO GENERATE INVOICE
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($model) {

            $count = self::count() + 1;

            $model->kode_invoice =
                str_pad($count, 6, '0', STR_PAD_LEFT)
                . '/INV/'
                . now()->format('mY');
        });
    }
}