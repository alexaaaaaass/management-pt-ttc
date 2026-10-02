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
    'order_type',
];

    public function itemable()
    {
        return $this->morphTo();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function material()
    {
        return $this->belongsTo(
            MasterItem::class,
            'material_item_id'
        );
    }

    public function getQtyDeliveredAttribute()
    {
        return $this->suratJalans()->sum('qty_pengiriman');
    }

    public function getQtyOutstandingAttribute()
    {
        return max(
            0,
            $this->qty - $this->qty_delivered
        );
    }

    public function items()
    {
        return $this->hasMany(
            SalesOrderItem::class
        );
    }

    public function getModeAttribute(): string
    {
        if ($this->itemable_type === FinishGoodItem::class) {

            return 'SPK';

        }

        return $this->items()->count() <= 1
            ? 'SINGLE_MATERIAL'
            : 'MULTI_MATERIAL';
    }

    public function getOrderQtyAttribute(): float
    {
        if ($this->mode === 'SPK') {

            return (float) ($this->qty ?? 0);

        }

        if ($this->items->count() === 1) {

            return (float) optional(
                $this->items->first()
            )->qty;

        }

        return (float) $this->items->sum('qty');
    }

    public function getProductNameAttribute(): string
    {
        if ($this->mode === 'SPK') {

            return $this->itemable?->nama_barang ?? '-';

        }

        if ($this->items->count() == 1) {

            return $this->items
                ->first()
                ?->material
                ?->nama_master_item
                ?? '-';

        }

        return $this->items
            ->pluck('material.nama_master_item')
            ->implode(', ');
    }

    public function getDeliveredQtyAttribute(): float
{
    if ($this->mode == 'SPK') {

        return $this->suratJalans()
            ->sum('qty_pengiriman');

    }

    if ($this->items->count() == 1) {

        return $this->items
            ->first()
            ?->suratJalanItems()
            ->sum('qty_kirim') ?? 0;

    }

    return $this->items
        ->sum(function ($item) {

            return $item
                ->suratJalanItems()
                ->sum('qty_kirim');

        });
}

public function getOutstandingQtyAttribute(): float
{
    return max(

        0,

        $this->order_qty
        -
        $this->delivered_qty

    );
}

public function getCurrentStockAttribute()
{
    if ($this->mode == 'SPK') {

        return optional(

            MasterSpk::where(
                'sales_order_id',
                $this->id
            )->first()

        )->on_hand_stock ?? 0;

    }

    $item = $this->items->first();

    if (! $item) {

        return 0;

    }

    return optional(

        MaterialStock::where(
            'item_id',
            $item->master_item_id
        )->first()

    )->on_hand ?? 0;
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