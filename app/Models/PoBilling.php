<?php

namespace App\Models;

use App\Models\PoBillingItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PoBilling extends Model
{
    protected $fillable = [

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

    public function penerimaanBarang()
    {
        return $this->belongsTo(
            PenerimaanBarang::class
        );
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(
            PurchaseOrder::class
        );
    }

    public function supplier()
    {
        return $this->belongsTo(
            MasterSupplier::class,
            'supplier_id'
        );
    }

    public function items()
    {
        return $this->hasMany(
            PoBillingItem::class
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

            $data->kode_tagihan =
                "BILL-{$date}-{$urut}";
        });
    }
}