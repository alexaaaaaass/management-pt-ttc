<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImrFinishItem extends Model
{
    protected $table = 'imr_finish_items';

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

    public function imr()
    {
        return $this->belongsTo(ImrFinish::class, 'imr_id');
    }

    public function item()
    {
        return $this->belongsTo(MasterItem::class, 'item_id');
    }

    public function getQtyRemainingAttribute(): float
    {
        return max(
            0,
            (float) $this->qty_approved - (float) $this->qty_issued
        );
    }

    public function getQtyAvailableReturnAttribute(): float
    {
        return max(
            0,
            (float) $this->qty_issued - (float) $this->qty_returned
        );
    }
}