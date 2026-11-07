<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinishGoodMovement extends Model
{
    protected $fillable = [
        'finish_good_item_id',
        'spk_id',
        'movement_type',
        'source',
        'qty',
        'before_stock',
        'after_stock',
        'reference_type',
        'reference_id',
        'user_id',
        'remarks',
    ];

    protected $casts = [
        'qty'          => 'float',
        'before_stock' => 'float',
        'after_stock'  => 'float',
    ];

    public function finishGoodItem()
    {
        return $this->belongsTo(FinishGoodItem::class, 'finish_good_item_id');
    }

    public function spk()
    {
        return $this->belongsTo(MasterSpk::class, 'spk_id');
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

