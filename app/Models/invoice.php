<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Services\Finance\InvoiceService;

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
        'total_payment' => 'float',
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
    | BOOTED & MODEL HOOKS
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode_invoice) || $model->kode_invoice === 'AUTO') {
                $model->kode_invoice = app(InvoiceService::class)->generateKodeInvoice();
            }
        });

        static::deleting(function ($model) {
            if ($model->payments()->exists()) {
                throw new \Exception('Tidak dapat menghapus Invoice karena sudah memiliki riwayat pembayaran.');
            }
        });
    }

    public function refreshPayment(): void
    {
        app(InvoiceService::class)->refreshInvoicePayment($this);
    }
}