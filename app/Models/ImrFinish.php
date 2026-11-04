<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImrFinish extends Model
{
    use HasFactory;

    protected $table = 'imr_finishes';

    protected $fillable = [
        'no_imr',
        'spk_id',
        'departemen_id',
        'tanggal_request',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal_request' => 'date',
    ];

    protected static function booted(): void
    {
        static::created(function (self $model) {
            if (!$model->no_imr) {
                $model->updateQuietly([
                    'no_imr' => 'IMR-FIN-'
                        . now()->format('Ym')
                        . '-'
                        . str_pad($model->id, 4, '0', STR_PAD_LEFT),
                ]);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function spk()
    {
        return $this->belongsTo(MasterSpk::class, 'spk_id');
    }

    public function departemen()
    {
        return $this->belongsTo(MasterDepartemen::class, 'departemen_id');
    }

    public function items()
    {
        return $this->hasMany(ImrFinishItem::class, 'imr_finish_id');
    }
}
