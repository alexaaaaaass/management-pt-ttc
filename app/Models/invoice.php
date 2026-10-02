<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Invoice extends Model
{
   protected $fillable = [

    'kode_invoice',

    'surat_jalan_id',

    'customer_id',
    'sales_order_id',
    'no_po_customer',

    'tanggal_invoice',
    'tanggal_jatuh_tempo',

    'subtotal',
    'diskon',
    'ppn',
    'ongkir',
    'uang_muka',

    'grand_total',
    'total_payment',
    'sisa_tagihan',

    'status',
    'is_printed',

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
            InvPayment::class,
            'invoice_id'
        );
    }

    public function customer()
    {
        return $this->belongsTo(
            \App\Models\Customer::class
        );
    }

    public function salesOrder()
    {
        return $this->belongsTo(
            \App\Models\SalesOrder::class
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

     public function refreshPayment(): void
    {
        $totalPayment = $this->payments()
            ->sum('nominal_bayar');

        $this->total_payment = $totalPayment;

        $this->sisa_tagihan =
            max(
                0,
                $this->grand_total - $totalPayment
            );

        if ($totalPayment <= 0) {

            $this->status = 'Unpaid';

        } elseif ($totalPayment < $this->grand_total) {

            $this->status = 'Partial';

        } else {

            $this->status = 'Paid';
        }

        $this->saveQuietly();
    }
}