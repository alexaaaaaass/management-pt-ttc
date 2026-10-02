<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterDepartemen;
class ImrPrinting extends Model
{
    protected $table = 'imr_printing';

    protected $fillable = [
        'no_imr',
        'spk_id',
        'departemen_id',
        'tanggal_request',
        'status',
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