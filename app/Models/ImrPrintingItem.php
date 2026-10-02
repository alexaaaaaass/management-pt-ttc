<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImrPrintingItem extends Model
{
    protected $table = 'imr_printing_items';

    protected $fillable = [

        'imr_id',

        'item_id',

        'total_pesanan',

        'qty_request',

        'qty_approved',

        'qty_issued',

        'qty_returned',

        'satuan',

        'catatan',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function imr()
    {
        return $this->belongsTo(
            ImrPrinting::class,
            'imr_id'
        );
    }

    public function item()
    {
        return $this->belongsTo(
            MasterItem::class,
            'item_id'
        );
    }

    public function internalReturnItems()
    {
        return $this->hasMany(
            InternalReturnItem::class,
            'imr_printing_item_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor
    |--------------------------------------------------------------------------
    */

    public function getQtyRemainingAttribute()
    {
        return max(
            0,
            $this->qty_approved
            - $this->qty_issued
            - $this->qty_returned
        );
    }

    public function getQtyAvailableReturnAttribute()
    {
        return max(
            0,
            $this->qty_issued
            - $this->qty_returned
        );
    }
}