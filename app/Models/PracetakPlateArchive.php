<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracetakPlateArchive extends Model
{
    protected $table = 'pracetak_plate_archives';

    protected $fillable = [
        'kode_arsip',
        'pracetak_process_id',
        'finish_good_item_id',
        'customer_id',
        'nama_plat',
        'jumlah_warna',
        'detail_warna',
        'dimensi_plate_mm',
        'lokasi_rak',
        'material_pendukung',
        'toleransi_rusak_hilang',
        'max_pemakaian',
        'jumlah_dipakai',
        'status_kondisi',
        'last_used_at',
        'catatan',
    ];

    protected $casts = [
        'jumlah_warna'   => 'integer',
        'max_pemakaian'  => 'integer',
        'jumlah_dipakai' => 'integer',
        'last_used_at'   => 'datetime',
    ];

    protected $attributes = [
        'status_kondisi' => 'tersimpan',
        'jumlah_dipakai' => 0,
    ];

    public function pracetakProcess(): BelongsTo
    {
        return $this->belongsTo(PracetakProcess::class, 'pracetak_process_id');
    }

    public function finishGoodItem(): BelongsTo
    {
        return $this->belongsTo(FinishGoodItem::class, 'finish_good_item_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function pracetakProcesses(): HasMany
    {
        return $this->hasMany(PracetakProcess::class, 'plate_archive_id');
    }

    public function getIsUsableAttribute(): bool
    {
        return in_array($this->status_kondisi, ['tersimpan', 'dipakai_printing'], true);
    }

    public function getDisplayLabelAttribute(): string
    {
        $rak = $this->lokasi_rak ?: 'Rak belum diisi';
        $cust = $this->customer?->nama_customer ? " · {$this->customer->nama_customer}" : '';

        return "{$this->kode_arsip} — {$this->nama_plat} ({$this->jumlah_warna} Warna · {$rak}{$cust})";
    }

    public static function generateKode(): string
    {
        $year = now()->format('Y');
        $prefix = "PLT-{$year}-";

        $last = static::where('kode_arsip', 'like', $prefix . '%')
            ->orderByDesc('kode_arsip')
            ->value('kode_arsip');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
