<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImrPrintingItem extends Model
{
    protected $table = 'imr_printing_items';

    protected $fillable = [
        'imr_id',
        'item_id',
        'department',
        'catatan',
        'satuan',
        'total_pesanan',
        'qty_approved',
        'qty_request',
        'qty_input',
        
    ];

    public function imr()
    {
        return $this->belongsTo(ImrPrinting::class);
    }

    public function item()
{
    return $this->belongsTo(\App\Models\MasterItem::class, 'item_id');
}
}