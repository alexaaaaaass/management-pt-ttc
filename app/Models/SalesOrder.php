<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SalesOrder extends Model
{
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

    protected static function booted()
    {
        static::creating(function ($model) {
            $last = self::max('id') + 1;
            $monthYear = Carbon::now()->format('mY');
            $kode = 'IK-10';

            $model->no_sales_order = $last . '/' . $kode . '/' . $monthYear;
        });
    }
}