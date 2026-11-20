<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracetakPenerimaan extends Model
{
    protected $table = 'pracetak_penerimaans';

    protected $fillable = [
        'nomor_penerimaan',
        'tanggal_terima',
        'supplier_id',
        'no_invoice_supplier',
        'no_surat_jalan',
        'status',
        'total_qty',
        'total_nominal',
        'catatan',
        'created_by',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'tanggal_terima' => 'date',
        'posted_at'      => 'datetime',
        'total_qty'      => 'decimal:2',
        'total_nominal'  => 'decimal:2',
    ];

    protected $attributes = [
        'status'        => 'draft',
        'total_qty'     => 0,
        'total_nominal' => 0,
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

    public function items(): HasMany
    {
        return $this->hasMany(PracetakPenerimaanItem::class, 'pracetak_penerimaan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }

    /**
     * Generate format nomor penerimaan: PRE-YYYY-0001
     */
    public static function generateNomorPenerimaan(?string $date = null): string
    {
        $year = $date ? Carbon::parse($date)->format('Y') : date('Y');
        $prefix = "PRE-{$year}-";

        $lastNumber = static::where('nomor_penerimaan', 'like', "{$prefix}%")
            ->orderByDesc('nomor_penerimaan')
            ->value('nomor_penerimaan');

        $nextSeq = 1;
        if ($lastNumber && preg_match('/-(\d+)$/', $lastNumber, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
