<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MasterSpk extends Model
{
    protected $table = 'master_spk';

   protected $fillable = [
    'no_spk',
    'kode_ik',
    'sales_order_id',
    'production_plan',
    'status',
    'tanggal_estimasi_selesai',
    'tanggal_po',

    // 🔥 tambahan
    'up_satu',
    'up_dua',
    'up_tiga',
    'ukuran_potong',
    'ukuran_cetak',
    'spesifikasi_kertas',
];

    // 🔥 RELASI LENGKAP STASIUN PRODUKSI
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function pracetaks()
    {
        return $this->hasMany(PracetakProcess::class, 'spk_id');
    }

    public function diemakings()
    {
        return $this->hasMany(Diemaking::class, 'spk_id');
    }

    public function printings()
    {
        return $this->hasMany(Printing::class, 'spk_id');
    }

    public function finishings()
    {
        return $this->hasMany(MasterFinishing::class, 'spk_id');
    }

    public function packagings()
    {
        return $this->hasMany(Packaging::class, 'spk_id');
    }

    public function suratJalans()
    {
        return $this->hasMany(SuratJalan::class, 'spk_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & HITUNGAN STOK
    |--------------------------------------------------------------------------
    */

    public function getOrderQtyAttribute(): int
    {
        return (int) ($this->salesOrder?->qty ?? 0);
    }

    public function getTotalGoodPackedAttribute(): int
    {
        return (int) $this->packagings()
            ->where('jenis_transfer', '!=', 'blokir')
            ->sum('grand_total');
    }

    public function getTotalBlockedPackedAttribute(): int
    {
        return (int) $this->packagings()
            ->where('jenis_transfer', 'blokir')
            ->sum('grand_total');
    }

    public function getTotalShippedAttribute(): int
    {
        return (int) $this->suratJalans()->sum('qty_pengiriman');
    }

    public function getOnHandStockAttribute(): int
    {
        return max(0, $this->total_good_packed - $this->total_shipped);
    }

    public function getCompletionRateAttribute(): float
    {
        $orderQty = $this->order_qty;
        if ($orderQty <= 0) {
            return 0.0;
        }

        return round(min(100.0, ($this->total_good_packed / $orderQty) * 100), 1);
    }

    public function getProductionProgressAttribute(): array
    {
        return app(\App\Services\Production\ProductionService::class)->getProductionProgress($this);
    }

    // 🔥 MODEL HOOKS & VALIDASI PROTEKSI
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->no_spk)) {
                $model->no_spk = app(\App\Services\Production\ProductionService::class)->generateNoSpk($model->kode_ik ?: '01');
            }

            if (empty($model->tanggal_po) && $model->sales_order_id) {
                $so = SalesOrder::find($model->sales_order_id);
                if ($so) {
                    $model->tanggal_po = $so->tanggal_po;
                }
            }
        });

        static::deleting(function (MasterSpk $spk) {
            if ($spk->suratJalans()->exists()) {
                throw new \Exception("SPK {$spk->no_spk} tidak dapat dihapus karena sudah memiliki riwayat Surat Jalan.");
            }
            if ($spk->packagings()->exists()) {
                throw new \Exception("SPK {$spk->no_spk} tidak dapat dihapus karena sudah ada hasil produksi packaging.");
            }
            if ($spk->finishings()->exists() || $spk->printings()->exists() || $spk->pracetaks()->exists()) {
                throw new \Exception("SPK {$spk->no_spk} tidak dapat dihapus karena sudah masuk antrean proses kerja lantai pabrik.");
            }
        });
    }

    
}