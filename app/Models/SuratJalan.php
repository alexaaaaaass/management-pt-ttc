<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratJalan extends Model
{
    use HasFactory;

    protected $fillable = [

        'kode_surat_jalan',

        'sales_order_id',

        'spk_id',

        // Khusus SPK
        'qty_pengiriman',

        'tanggal_surat_jalan',

        'alamat_tujuan',

        'transportasi',

        'no_polisi',

        'driver',

        'pengirim',

        'keterangan',

    ];

    public function salesOrder()
    {
        return $this->belongsTo(
            SalesOrder::class
        );
    }

    public function spk()
    {
        return $this->belongsTo(
            MasterSpk::class,
            'spk_id'
        );
    }

    public function invoice()
    {
        return $this->hasOne(
            Invoice::class
        );
    }

    public function items()
    {
        return $this->hasMany(
            SuratJalanItem::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BOOTED & MODEL HOOKS
    |--------------------------------------------------------------------------
    */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->kode_surat_jalan)) {
                $tanggal = Carbon::now()->format('Ymd');
                $count = self::whereDate('created_at', today())->count() + 1;

                $model->kode_surat_jalan = 'SJ-' . $tanggal . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }
        });

        static::deleting(function (SuratJalan $suratJalan) {
            if ($suratJalan->invoice()->exists()) {
                throw new \Exception('Tidak dapat menghapus Surat Jalan karena sudah memiliki Invoice terkait.');
            }

            // Kembalikan stok material bila Surat Jalan dibatalkan/dihapus
            $suratJalan->loadMissing('items');
            $stockService = app(\App\Services\Inventory\StockService::class);

            foreach ($suratJalan->items as $item) {
                if ($item->qty_kirim > 0 && $item->master_item_id) {
                    $stockService->increaseStock(
                        itemId: $item->master_item_id,
                        qty: (float) $item->qty_kirim,
                        reference: $suratJalan,
                        remarks: 'Rollback Hapus Surat Jalan - ' . $suratJalan->kode_surat_jalan
                    );
                }
            }

            $suratJalan->items()->delete();
        });
    }
}