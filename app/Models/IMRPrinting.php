<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterDepartemen;
class IMRPrinting extends Model
{
    public const STASIUN_PRINTING = 'printing';

    public const STASIUN_DIEMAKING = 'diemaking';

    protected $table = 'imr_printing';

    protected $fillable = [
        'no_imr',
        'stasiun',
        'spk_id',
        'pracetak_process_id',
        'plate_archive_id',
        'departemen_id',
        'tanggal_request',
        'status',
        'request_plate',
        'plate_qty_requested',
        'plate_approved',
        'plate_issued_at',
        'plate_received_by',
        'plate_notes',
    ];

    protected $attributes = [
        'stasiun' => self::STASIUN_PRINTING,
    ];

    protected $casts = [
        'request_plate'       => 'boolean',
        'plate_approved'      => 'boolean',
        'plate_qty_requested' => 'integer',
        'plate_issued_at'     => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (self $model) {
            if (empty($model->no_imr)) {
                $prefix = $model->stasiun === self::STASIUN_DIEMAKING ? 'IMRD-' : 'IMR-';
                $model->updateQuietly([
                    'no_imr' => $prefix
                        . now()->format('Ym')
                        . '-'
                        . str_pad((string) $model->id, 4, '0', STR_PAD_LEFT),
                ]);
            }
        });
    }

    public function getStasiunLabelAttribute(): string
    {
        return match ($this->stasiun) {
            self::STASIUN_DIEMAKING => 'Die Making',
            default                 => 'Printing',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function spk()
    {
        return $this->belongsTo(
            MasterSpk::class,
            'spk_id'
        );
    }

    public function pracetakProcess()
    {
        return $this->belongsTo(
            PracetakProcess::class,
            'pracetak_process_id'
        );
    }

    public function plateArchive()
    {
        return $this->belongsTo(
            PracetakPlateArchive::class,
            'plate_archive_id'
        );
    }

    public function hasPendingPlateIssue(): bool
    {
        return (bool) $this->request_plate
            && (bool) $this->plate_approved
            && is_null($this->plate_issued_at);
    }

    public function isPlateIssued(): bool
    {
        return (bool) $this->request_plate
            && ! is_null($this->plate_issued_at);
    }

    public function departemen()
    {
        return $this->belongsTo(
            MasterDepartemen::class,
            'departemen_id'
        );
    }

    public function items()
    {
        return $this->hasMany(
            ImrPrintingItem::class,
            'imr_id'
        );
    }

    public function internalReturns()
    {
        return $this->hasMany(
            InternalReturn::class,
            'imr_id'
        );
    }

    public function movements()
    {
        return $this->morphMany(
            MaterialMovement::class,
            'reference'
        );
    }
}