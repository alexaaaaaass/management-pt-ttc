<?php

namespace App\Models;

use App\Models\PurchaseRequestItem;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    protected $fillable = [
        'departemen_id',
        'tanggal_pr'
    ];

    public function departemen()
    {
        return $this->belongsTo(MasterDepartemen::class,'departemen_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }
}