<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinishGoodStock extends Model
{
    protected $fillable = [
        'finish_good_item_id',
        'on_hand',
        'blocked',
    ];

    protected $casts = [
        'on_hand' => 'float',
        'blocked' => 'float',
    ];

    public function finishGoodItem()
    {
        return $this->belongsTo(FinishGoodItem::class, 'finish_good_item_id');
    }

    public function movements()
    {
        return $this->hasMany(
            FinishGoodMovement::class,
            'finish_good_item_id',
            'finish_good_item_id'
        );
    }

    public function getAvailableAttribute(): float
    {
        return max(0.0, (float) $this->on_hand);
    }
}

