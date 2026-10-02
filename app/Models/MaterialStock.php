<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialStock extends Model
{
    protected $fillable = [
        'item_id',
        'on_hand',
        'allocation',
    ];

    public function item()
    {
        return $this->belongsTo(
            MasterItem::class,
            'item_id'
        );
    }

    public function movements()
    {
        return $this->hasManyThrough(

            MaterialMovement::class,

            MasterItem::class,

            'id',

            'item_id',

            'item_id',

            'id'

        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor
    |--------------------------------------------------------------------------
    */

    public function getAvailableAttribute(): float
    {
        return max(
            0,
            $this->on_hand - $this->allocation
        );
    }

    public function getOutstandingAttribute(): float
    {
        return $this->available;
    }
}