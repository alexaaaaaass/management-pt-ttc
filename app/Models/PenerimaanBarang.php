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
        static::creating(function ($data) {
            $date = Carbon::now()->format('Ymd');

            $count = self::whereDate('created_at', now())->count() + 1;

            $urutan = str_pad($count, 3, '0', STR_PAD_LEFT);

            $data->no_penerimaan = "PB-{$date}-{$urutan}";
        });
    }
    
}