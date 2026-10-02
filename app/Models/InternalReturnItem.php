<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalReturnItem extends Model
{
    use HasFactory;

    public function internalReturn()
{
    return $this->belongsTo(
        InternalReturn::class
    );
}

public function imrItem()
{
    return $this->belongsTo(
        ImrPrintingItem::class,
        'imr_printing_item_id'
    );
}
}