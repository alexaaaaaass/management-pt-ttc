<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImrFinishItem extends Model
{
    use HasFactory;

    protected $table = 'imr_finish_items';

    protected $fillable = [
        'imr_finish_id',
        'item_id',
        'total_pesanan',
        'qty_request',
        'qty_approved',
        'qty_issued',
        'qty_returned',
        'satuan',
        'catatan',
    ];

    protected $casts = [
        'total_pesanan' => 'float',
        'qty_request'   => 'float',
        'qty_approved'  => 'float',
        'qty_issued'    => 'float',
        'qty_returned'  => 'float',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function imrFinish()
    {
        return $this->belongsTo(ImrFinish::class, 'imr_finish_id');
    }

    public function item()
    {
        return $this->belongsTo(MasterItem::class, 'item_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getQtyRemainingAttribute(): float
    {
        return max(
            0,
            $this->qty_approved - $this->qty_issued - $this->qty_returned
        );
    }

    public function getQtyAvailableReturnAttribute(): float
    {
        return max(
            0,
            $this->qty_issued - $this->qty_returned
        );
    }
}
