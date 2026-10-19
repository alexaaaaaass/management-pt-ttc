<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'internal_return_id',
        'imr_printing_item_id',
        'item_id',
        'qty_return',
        'catatan',
    ];

    protected $casts = [
        'qty_return' => 'decimal:2',
    ];

    public function internalReturn()
    {
        return $this->belongsTo(InternalReturn::class);
    }

    public function imrItem()
    {
        return $this->belongsTo(ImrPrintingItem::class, 'imr_printing_item_id');
    }

    public function item()
    {
        return $this->belongsTo(MasterItem::class, 'item_id');
    }
}