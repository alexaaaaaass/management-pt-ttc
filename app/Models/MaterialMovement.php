<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialMovement extends Model
{
        protected $fillable = [

        'item_id',

        'created_by',

        'reference_type',

        'reference_id',

        'movement_type',

        'qty',

        'stock_before',

        'stock_after',

        'remarks',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function item()
    {
        return $this->belongsTo(
            MasterItem::class,
            'item_id'
        );
    }

   public function user()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    public function getReferenceAttribute()
    {
        return "{$this->reference_type}-{$this->reference_id}";
    }
}