<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracetakPenerimaanItem extends Model
{
    protected $table = 'pracetak_penerimaan_items';

    protected $fillable = [
        'pracetak_penerimaan_id',
        'master_material_plate_id',
        'brand',
        'tipe_plate',
        'ketebalan_mm',
        'panjang_sheet_mm',
        'lebar_sheet_mm',
        'qty_sheet',
        'harga_satuan',
        'subtotal',
        'catatan',
    ];

    protected $casts = [
        'ketebalan_mm'     => 'decimal:3',
        'panjang_sheet_mm' => 'decimal:2',
        'lebar_sheet_mm'   => 'decimal:2',
        'qty_sheet'        => 'decimal:2',
        'harga_satuan'     => 'decimal:2',
        'subtotal'         => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function penerimaan(): BelongsTo
    {
        return $this->belongsTo(PracetakPenerimaan::class, 'pracetak_penerimaan_id');
    }

    public function masterPlate(): BelongsTo
    {
        return $this->belongsTo(MasterMaterialPlate::class, 'master_material_plate_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getUkuranSheetAttribute(): string
    {
        $p = rtrim(rtrim(number_format((float) $this->panjang_sheet_mm, 2, '.', ','), '0'), '.');
        $l = rtrim(rtrim(number_format((float) $this->lebar_sheet_mm, 2, '.', ','), '0'), '.');

        return "{$p} × {$l} mm";
    }

    protected static function booted()
    {
        parent::booted();

        static::saving(function ($item) {
            // Snapshot spesifikasi dari Master Material Plate jika belum terisi
            if ($item->masterPlate && empty($item->brand)) {
                $plate = $item->masterPlate;
                $item->brand = $plate->brand;
                $item->tipe_plate = $plate->tipe_plate;
                $item->ketebalan_mm = $plate->ketebalan_mm;
                $item->panjang_sheet_mm = $plate->panjang_sheet_mm;
                $item->lebar_sheet_mm = $plate->lebar_sheet_mm;
            }

            // Hitung subtotal otomatis
            $item->subtotal = round(((float) $item->qty_sheet) * ((float) $item->harga_satuan), 2);
        });
    }
}
