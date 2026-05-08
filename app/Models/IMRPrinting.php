<?php

namespace App\Models;

use App\Models\ImrPrintingItem;
use Illuminate\Database\Eloquent\Model;

class ImrPrinting extends Model
{
    protected $table = 'imr_printing';

    protected $fillable = [
        'no_imr',
        'spk_id',
        'tanggal_request',
    ];

    public function spk()
    {
        return $this->belongsTo(MasterSpk::class, 'spk_id');
    }

    public function items()
    {
        return $this->hasMany(ImrPrintingItem::class, 'imr_id');
    }

   protected static function booted()
{
    static::created(function ($model) {

        $model->no_imr =
            'IMR-' . now()->format('Ym') . '-' .
            str_pad($model->id, 4, '0', STR_PAD_LEFT);

        $model->saveQuietly();
    });
}
}