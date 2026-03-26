<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SalesOrder extends Model
{
   protected $fillable = [
    'item_type',
    'item_id',
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

    // 🔥 RELASI
    public function finishGoodItem()
    {
        return $this->belongsTo(FinishGoodItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // 🔥 AUTO GENERATE NO SO
    protected static function booted()
    {
        static::creating(function ($model) {

            $last = self::max('id') + 1;

            $monthYear = Carbon::now()->format('mY'); // 032026
            $kode = 'IK-10';

            $model->no_sales_order = $last . '/' . $kode . '/' . $monthYear;
        });
    }

    public function item()
{
    if ($this->item_type === 'finish_good') {
        return $this->belongsTo(FinishGoodItem::class, 'item_id');
    }

    return $this->belongsTo(MasterItem::class, 'item_id');
}
}