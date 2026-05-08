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
        return $this->belongsTo(MasterItem::class, 'item_id');
    }

    // 🔥 helper
    public function getOutstandingAttribute()
    {
        return $this->on_hand - $this->allocation;
    }
}