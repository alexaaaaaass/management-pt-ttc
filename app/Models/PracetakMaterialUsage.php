<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracetakMaterialUsage extends Model
{
    protected $table = 'pracetak_material_usages';

    protected $fillable = [
        'pracetak_process_id',
        'master_material_plate_id',
        'jenis_pemakaian',
        'jumlah_plate',
        'panjang_potong_mm',
        'lebar_potong_mm',
        'luas_terpakai_cm2',
        'qty_sheet_terpakai',
        'harga_satuan_snapshot',
        'total_biaya',
        'status',
        'alasan_kerusakan',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'jumlah_plate'          => 'integer',
        'panjang_potong_mm'     => 'decimal:2',
        'lebar_potong_mm'       => 'decimal:2',
        'luas_terpakai_cm2'     => 'decimal:2',
        'qty_sheet_terpakai'    => 'decimal:4',
        'harga_satuan_snapshot' => 'decimal:2',
        'total_biaya'           => 'decimal:2',
        'posted_at'             => 'datetime',
    ];

    protected $attributes = [
        'jenis_pemakaian' => 'produksi',
        'status'          => 'draft',
    ];

    public function pracetakProcess(): BelongsTo
    {
        return $this->belongsTo(PracetakProcess::class, 'pracetak_process_id');
    }

    public function masterPlate(): BelongsTo
    {
        return $this->belongsTo(MasterMaterialPlate::class, 'master_material_plate_id');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function getJenisLabelAttribute(): string
    {
        return match ($this->jenis_pemakaian) {
            'produksi'     => 'Produksi Plate',
            'remake_rusak' => 'Remake / Plate Rusak',
            'prove'        => 'Prove Print',
            default        => ucfirst((string) $this->jenis_pemakaian),
        };
    }

    protected static function booted(): void
    {
        static::deleting(function (PracetakMaterialUsage $usage) {
            if ($usage->status === 'posted') {
                throw new \DomainException('Baris pemakaian material plate yang sudah diposting ke stok tidak dapat dihapus langsung. Gunakan aksi Reversal jika perlu koreksi.');
            }
        });
    }
}
