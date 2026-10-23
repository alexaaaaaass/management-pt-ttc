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

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
    
    public static function penyebut($nilai)
    {
        $nilai = abs($nilai);
        $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
        $temp = "";
        if ($nilai < 12) {
            $temp = " " . $huruf[$nilai];
        } else if ($nilai < 20) {
            $temp = self::penyebut($nilai - 10) . " Belas";
        } else if ($nilai < 100) {
            $temp = self::penyebut(floor($nilai / 10)) . " Puluh" . self::penyebut($nilai % 10);
        } else if ($nilai < 200) {
            $temp = " Seratus" . self::penyebut($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = self::penyebut(floor($nilai / 100)) . " Ratus" . self::penyebut($nilai % 100);
        } else if ($nilai < 2000) {
            $temp = " Seribu" . self::penyebut($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = self::penyebut(floor($nilai / 1000)) . " Ribu" . self::penyebut($nilai % 1000);
        } else if ($nilai < 1000000000) {
            $temp = self::penyebut(floor($nilai / 1000000)) . " Juta" . self::penyebut($nilai % 1000000);
        } else if ($nilai < 1000000000000) {
            $temp = self::penyebut(floor($nilai / 1000000000)) . " Milyar" . self::penyebut(fmod($nilai, 1000000000));
        } else if ($nilai < 1000000000000000) {
            $temp = self::penyebut(floor($nilai / 1000000000000)) . " Triliun" . self::penyebut(fmod($nilai, 1000000000000));
        }
        return $temp;
    }

    public function getTerbilangFormattedAttribute()
    {
        if (!empty($this->terbilang_override)) {
            return $this->terbilang_override;
        }

        $target = $this->sisa_tagihan > 0 ? $this->sisa_tagihan : $this->grand_total;
        if ($target <= 0) return 'Nol Rupiah';

        $hasil = trim(self::penyebut($target));
        return preg_replace('/\s+/', ' ', $hasil) . ' Rupiah';
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

        // Proteksi hapus ditangani oleh InvoiceObserver::deleting()
        // untuk menghindari duplikasi pengecekan.
    }

    public function refreshPayment(): void
    {
        app(InvoiceService::class)->refreshInvoicePayment($this);
    }
}