<?php

namespace App\Models;

use App\Models\PoBillingItem;
use App\Models\PoBillPay;
use App\Services\Finance\PoBillingService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PoBilling extends Model
{
    protected $fillable = [
        'kode_tagihan',
        'penerimaan_barang_id',
        'purchase_order_id',
        'supplier_id',
        'karyawan_id',
        'invoice_vendor',
        'periode',
        'tanggal_transaksi',
        'tanggal_jatuh_tempo',
        'keterangan',
        'total_barang',
        'ppn',
        'ongkir',
        'dp',
        'grand_total',
    ];

    protected $casts = [
        'tanggal_transaksi'   => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'total_barang'        => 'float',
        'ppn'                 => 'float',
        'ongkir'              => 'float',
        'dp'                  => 'float',
        'grand_total'         => 'float',
    ];

    public function penerimaanBarang()
    {
        return $this->belongsTo(PenerimaanBarang::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier()
    {
        return $this->belongsTo(MasterSupplier::class, 'supplier_id');
    }

    public function items()
    {
        return $this->hasMany(PoBillingItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PoBillPay::class, 'po_billing_id');
    }

    public function getTotalPaymentAttribute(): float
    {
        return (float) $this->payments()->sum('total_pembayaran');
    }

    public function getSisaTagihanAttribute(): float
    {
        return max(0.0, round((float) $this->grand_total - $this->total_payment, 2));
    }

    public function getStatusAttribute(): string
    {
        if ($this->sisa_tagihan <= 0.0 && (float) $this->grand_total > 0) {
            return 'Paid';
        } elseif ($this->total_payment > 0.0) {
            return 'Partial';
        }
        return 'Unpaid';
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode_tagihan) || $model->kode_tagihan === 'AUTO') {
                $model->kode_tagihan = app(PoBillingService::class)->generateKodeTagihan();
            }
        });

        static::deleting(function ($model) {
            if ($model->payments()->exists()) {
                throw new \Exception('Tidak dapat menghapus Tagihan Supplier karena sudah memiliki riwayat pembayaran.');
            }
        });
    }
}