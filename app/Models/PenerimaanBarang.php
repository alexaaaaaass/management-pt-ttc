<?php

namespace App\Models;

use App\Models\PenerimaanBarangItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;


class PenerimaanBarang extends Model
{
    protected $table = 'penerimaan_barangs';

    protected $fillable = [

    'no_penerimaan',

    'purchase_order_id',

    'tanggal_terima',

    'no_surat_jalan',

    'nama_pengirim',

    'nopol_kendaraan',

    'catatan_pengiriman',

];
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items()
    {
        return $this->hasMany(PenerimaanBarangItem::class);
    }



protected static function booted()
{
    parent::booted();

    static::creating(function ($pb) {

        $date = now()->format('Ymd');

        $count = self::whereDate('created_at', now())
            ->count() + 1;

        $pb->no_penerimaan =
            'PB-'.$date.'-'.str_pad($count,3,'0',STR_PAD_LEFT);

    });

}

public function processReceiving(): void
{
    $this->loadMissing('items.purchaseOrderItem');

    foreach ($this->items as $item) {

        $poItem = $item->purchaseOrderItem;

        if (! $poItem) {
            continue;
        }

        $stock = MaterialStock::firstOrCreate(
            ['item_id' => $poItem->item_id],
            ['on_hand' => 0, 'allocation' => 0]
        );

        $stock->increment('on_hand', $item->qty_terima);

        $poItem->increment('qty_received', $item->qty_terima);
    }
}
    
}