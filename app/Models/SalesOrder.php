<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{

 use SoftDeletes;
    protected $fillable = [
    'itemable_type',
    'itemable_id',
    'kode_material',
    'customer_id',
    'no_sales_order',
    'no_po_customer',
    'qty',
    'harga_pcs',
    'harga_kirim',
    'mata_uang',
    'syarat_pembayaran',
    'tanggal_po',
    'klaim_kertas',
    'dipesan_via',
    'tipe_pesanan',
    'toleransi_pengiriman',
    'catatan',
    'catatan_colour_range',
];

    public function itemable()
    {
        return $this->morphTo();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function suratJalans()
    {
        return $this->hasMany(SuratJalan::class);
    }
    protected static function booted()
    {
    static::creating(function ($model) {
        $model->no_sales_order = 'TEMP-' . uniqid();
    });

    static::created(function ($model) {
        $model->no_sales_order =
            str_pad($model->id, 4, '0', STR_PAD_LEFT)
            . '/IK-10/' . now()->format('mY');

        $model->saveQuietly();
    });
    }
}