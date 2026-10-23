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
    'lead_time',
    'diskon',
    'jenis',
    'komponen',
    'isi',
    'kertas',
    'warna',
    'finishing',
    'catatan',
    'catatan_colour_range',
    'order_type',
];

    public function getEffectiveJenisAttribute(): string
    {
        return $this->jenis ?: ($this->itemable?->jenis ?? '-');
    }

    public function getEffectiveKomponenAttribute(): string
    {
        return $this->komponen ?: ($this->itemable?->komponen ?? ($this->itemable?->typeItem?->nama_type_item ?? '-'));
    }

    public function getEffectiveIsiAttribute(): string
    {
        return $this->isi ?: ($this->itemable?->isi ?? '-');
    }

    public function getEffectiveKertasAttribute(): string
    {
        return $this->kertas ?: ($this->itemable?->spesifikasi_kertas ?? '-');
    }

    public function getEffectiveWarnaAttribute(): string
    {
        return $this->warna ?: ($this->itemable?->warna ?? '-');
    }

    public function getEffectiveFinishingAttribute(): string
    {
        return $this->finishing ?: ($this->itemable?->finishing ?? '-');
    }

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

    public function spk()
    {
        return $this->hasOne(MasterSpk::class, 'sales_order_id');
    }

    public function suratJalans()
    {
        return $this->hasMany(SuratJalan::class);
    }

    public function getFulfillmentAttribute(): array
    {
        return app(\App\Services\Sales\SalesOrderService::class)->getFulfillmentStatus($this);
    }

    public function getFinancialTotalsAttribute(): array
    {
        return app(\App\Services\Sales\SalesOrderService::class)->calculateTotals($this);
    }

    /**
     * Scope query untuk filter status pemenuhan pengiriman (Fulfillment Status).
     */
    public function scopeWhereFulfillmentStatus($query, string $status)
    {
        $spkDelivered = "(SELECT COALESCE(SUM(qty_pengiriman), 0) FROM surat_jalans WHERE surat_jalans.sales_order_id = sales_orders.id)";
        $materialOrder = "(SELECT COALESCE(SUM(qty), 0) FROM sales_order_items WHERE sales_order_items.sales_order_id = sales_orders.id)";
        $materialDelivered = "(SELECT COALESCE(SUM(sji.qty_kirim), 0) FROM surat_jalan_items sji JOIN sales_order_items soi ON sji.sales_order_item_id = soi.id WHERE soi.sales_order_id = sales_orders.id)";

        $effectiveDelivered = "CASE WHEN sales_orders.order_type = 'SPK' THEN $spkDelivered ELSE $materialDelivered END";
        $effectiveOrder = "CASE WHEN sales_orders.order_type = 'SPK' THEN COALESCE(sales_orders.qty, 0) ELSE $materialOrder END";

        if ($status === 'BELUM DIKIRIM') {
            return $query->whereRaw("($effectiveDelivered) <= 0");
        } elseif ($status === 'SELESAI') {
            return $query->whereRaw("($effectiveDelivered) > 0 AND ($effectiveDelivered) >= ($effectiveOrder)");
        } elseif ($status === 'DIKIRIM SEBAGIAN') {
            return $query->whereRaw("($effectiveDelivered) > 0 AND ($effectiveDelivered) < ($effectiveOrder)");
        }

        return $query;
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->no_sales_order) || str_starts_with($model->no_sales_order, 'TEMP-')) {
                $model->no_sales_order = app(\App\Services\Sales\SalesOrderService::class)->generateNoSalesOrder();
            }
        });

        static::deleting(function (SalesOrder $so) {
            if ($so->suratJalans()->exists()) {
                throw new \Exception('Sales Order tidak dapat dihapus karena sudah memiliki Surat Jalan.');
            }

            if ($so->spk()->exists()) {
                throw new \Exception('Sales Order tidak dapat dihapus karena sudah memiliki SPK produksi terkait.');
            }
        });
    }
}