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

//  protected static function booted()
// {
//     // 🔥 generate nomor SO
//     static::creating(function ($model) {
//         $last = self::max('id') + 1;
//         $monthYear = now()->format('mY');
//         $kode = 'IK-10';

//         $model->no_sales_order = $last . '/' . $kode . '/' . $monthYear;
//     });

//     // 🔥 TAMBAH allocation saat SO dibuat
//     static::created(function ($model) {
//         if ($model->itemable_type === \App\Models\FinishGoodItem::class) {

//             $finishGood = $model->itemable;

//             foreach ($finishGood->materials as $bom) {

//                 $kebutuhan = $model->qty * $bom->qty;

//                 $stock = \App\Models\MaterialStock::firstOrCreate(
//                     ['item_id' => $bom->master_item_id],
//                     ['on_hand' => 0, 'allocation' => 0]
//                 );

//                 $stock->increment('allocation', ceil($kebutuhan));
//             }
//         }
//     });

//     // 🔥 KURANGI allocation saat SO dihapus
//     static::deleted(function ($model) {
//         if ($model->itemable_type === \App\Models\FinishGoodItem::class) {

//             $finishGood = $model->itemable;

//             foreach ($finishGood->materials as $bom) {

//                 $kebutuhan = $model->qty * $bom->qty;

//                 $stock = \App\Models\MaterialStock::where(
//                     'item_id',
//                     $bom->master_item_id
//                 )->first();

//                 if ($stock) {
//                     $stock->decrement('allocation', ceil($kebutuhan));
//                 }
//             }
//         }
//     });

//     static::restored(function ($model) {
//     if ($model->itemable_type === \App\Models\FinishGoodItem::class) {

//         $finishGood = $model->itemable;

//         foreach ($finishGood->materials as $bom) {

//             $kebutuhan = $model->qty * $bom->qty;

//             $stock = \App\Models\MaterialStock::where(
//                 'item_id',
//                 $bom->master_item_id
//             )->first();

//             if ($stock) {
//                 $stock->increment('allocation', ceil($kebutuhan));
//             }
//         }
//     }
// });

// static::updating(function ($model) {
//     if ($model->isDirty('qty') && $model->itemable_type === \App\Models\FinishGoodItem::class) {

//         $oldQty = $model->getOriginal('qty');
//         $newQty = $model->qty;
//         $selisih = $newQty - $oldQty;

//         $finishGood = $model->itemable;

//         foreach ($finishGood->materials as $bom) {

//             $kebutuhan = $selisih * $bom->qty;

//             $stock = \App\Models\MaterialStock::where(
//                 'item_id',
//                 $bom->master_item_id
//             )->first();

//             if ($stock) {
//                 if ($selisih > 0) {
//                     $stock->increment('allocation', ceil($kebutuhan));
//                 } else {
//                     $stock->decrement('allocation', abs(ceil($kebutuhan)));
//                 }
//             }
//         }
//     }
// });
// }
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