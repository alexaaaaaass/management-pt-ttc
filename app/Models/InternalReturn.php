<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalReturn extends Model
{
    use HasFactory;

    public function items()
    {
        return $this->hasMany(
            InternalReturnItem::class
        );
    }

    public function imr()
    {
        return $this->belongsTo(
            ImrPrinting::class,
            'imr_id'
        );
    }
}