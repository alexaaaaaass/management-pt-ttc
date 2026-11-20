<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracetakMaterialStock extends Model
{
    protected $table = 'pracetak_material_stocks';

    protected $fillable = [
        'master_material_plate_id',
        'stok_sheet_utuh',
        'total_masuk',
        'total_keluar',
        'total_nilai_stok',
        'harga_terakhir',
        'last_movement_at',
    ];

    protected $casts = [
        'stok_sheet_utuh'  => 'decimal:2',
        'total_masuk'      => 'decimal:2',
        'total_keluar'     => 'decimal:2',
        'total_nilai_stok' => 'decimal:2',
        'harga_terakhir'   => 'decimal:2',
        'last_movement_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function plate(): BelongsTo
    {
        return $this->belongsTo(MasterMaterialPlate::class, 'master_material_plate_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Estimasi harga per sheet rata-rata berjalan (Weighted Average)
     */
    public function getHargaRataRataAttribute(): float
    {
        $qty = (float) $this->stok_sheet_utuh;
        if ($qty > 0) {
            return round(((float) $this->total_nilai_stok) / $qty, 2);
        }

        return (float) $this->harga_terakhir;
    }
}
