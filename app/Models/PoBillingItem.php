<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoBillingItem extends Model
{
    protected $fillable = [

        'po_billing_id',
        'item_id',
        'item_name',
        'qty',
        'harga',
        'diskon',
        'subtotal',

    ];

    public function billing()
    {
        return $this->belongsTo(
            PoBilling::class,
            'po_billing_id'
        );
    }
}