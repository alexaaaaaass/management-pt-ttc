<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterDepartemen;
class IMRPrinting extends Model
{
    protected $table = 'imr_printing';

    protected $fillable = [
        'no_imr',
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

    protected $casts = [
        'request_plate'       => 'boolean',
        'plate_approved'      => 'boolean',
        'plate_qty_requested' => 'integer',
        'plate_issued_at'     => 'datetime',
    ];

    protected static function booted(): void
{
    static::created(function (self $model) {

        $model->updateQuietly([
            'no_imr' =>
                'IMR-'
                . now()->format('Ym')
                . '-'
                . str_pad($model->id, 4, '0', STR_PAD_LEFT),
        ]);

    });
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