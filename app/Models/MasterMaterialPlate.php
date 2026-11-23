<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterMaterialPlate extends Model
{
    protected $table = 'master_material_plates';

    protected $fillable = [
        'master_item_id',
        'kode_material',
        'nama_material',
        'brand',
        'tipe_plate',
        'ketebalan_mm',
        'panjang_sheet_mm',
        'lebar_sheet_mm',
        'satuan_id',
        'harga_beli',
        'supplier_id',
        'status',
        'catatan',
    ];

    protected $casts = [
        'ketebalan_mm'     => 'decimal:3',
        'panjang_sheet_mm' => 'decimal:2',
        'lebar_sheet_mm'   => 'decimal:2',
        'harga_beli'       => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'aktif',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(MasterSupplier::class, 'supplier_id');
    }

    public function masterItem(): BelongsTo
    {
        return $this->belongsTo(MasterItem::class, 'master_item_id');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }

    public function stock(): HasOne
    {
        return $this->hasOne(PracetakMaterialStock::class, 'master_material_plate_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(PracetakMaterialMovement::class, 'master_material_plate_id');
    }

    public function penerimaanItems(): HasMany
    {
        return $this->hasMany(PracetakPenerimaanItem::class, 'master_material_plate_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Penanda apakah material sudah dipakai di transaksi Pra Cetak.
     * Mencegah delete jika material telah memiliki histori mutasi atau penerimaan.
     */
    public function isUsedInTransactions(): bool
    {
        try {
            return $this->movements()->exists() || $this->penerimaanItems()->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getUkuranSheetAttribute(): string
    {
        $p = rtrim(rtrim(number_format((float) $this->panjang_sheet_mm, 2, '.', ','), '0'), '.');
        $l = rtrim(rtrim(number_format((float) $this->lebar_sheet_mm, 2, '.', ','), '0'), '.');

        return "{$p} × {$l} mm";
    }

    /**
     * Saran kode material berikutnya: MPL-0001, MPL-0002, dst.
     */
    public static function suggestKode(): string
    {
        $last = static::where('kode_material', 'like', 'MPL-%')
            ->orderByDesc('kode_material')
            ->value('kode_material');

        $next = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'MPL-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Sinkronkan entri pasangan di master_items agar plate mentah dapat dibeli melalui alur baku PR -> PO -> LPB -> PO Billing.
     */
    public function syncLinkedMasterItem(): ?MasterItem
    {
        if (! \Illuminate\Support\Facades\Schema::hasColumn('master_material_plates', 'master_item_id')) {
            return null;
        }

        $category = CategoryItem::firstOrCreate(
            ['kode_item_category' => 'PLT'],
            ['nama_category' => 'Material Plate (Pra Cetak)']
        );

        $type = TypeItem::firstOrCreate(
            ['kode_type_item' => 'PLT-RAW'],
            [
                'nama_type_item'   => 'Material Plate Mentah',
                'category_item_id' => $category->id,
            ]
        );

        $satuanId = $this->satuan_id
            ?: MasterSatuan::where('nama_satuan', 'like', '%Sheet%')
                ->orWhere('nama_satuan', 'like', '%Lembar%')
                ->value('id')
            ?: MasterSatuan::query()->value('id');

        if (! $satuanId) {
            return null;
        }

        $displayName = "[PLATE] {$this->nama_material} ({$this->ukuran_sheet})";
        $payload = [
            'kode_master_item' => $this->kode_material,
            'nama_master_item' => $displayName,
            'satuan_id'        => $satuanId,
            'category_item_id' => $category->id,
            'type_item_id'     => $type->id,
            'panjang'          => $this->panjang_sheet_mm,
            'lebar'            => $this->lebar_sheet_mm,
            'tinggi'           => $this->ketebalan_mm,
        ];

        $masterItem = $this->master_item_id ? MasterItem::find($this->master_item_id) : null;
        if (! $masterItem) {
            $masterItem = MasterItem::where('kode_master_item', $this->kode_material)->first();
        }

        if ($masterItem) {
            $masterItem->update($payload);
        } else {
            $masterItem = MasterItem::create($payload);
        }

        if ((int) $this->master_item_id !== (int) $masterItem->id) {
            $this->updateQuietly(['master_item_id' => $masterItem->id]);
        }

        return $masterItem;
    }

    protected static function booted(): void
    {
        static::saved(function (MasterMaterialPlate $plate) {
            try {
                $plate->syncLinkedMasterItem();
            } catch (\Throwable $e) {
                // Abaikan jika tabel referensi belum siap saat migrasi awal
            }
        });
    }
}
