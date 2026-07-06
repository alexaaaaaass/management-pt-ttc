<?php

namespace App\Models;

use App\Models\PurchaseRequestItem;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
protected $fillable = [
    'nomor_pr',
    'departemen_id',
    'tanggal_pr',
    'status',
];

public function departemen()
{
    return $this->belongsTo(MasterDepartemen::class,'departemen_id');
}

public function items()
{
    return $this->hasMany(PurchaseRequestItem::class);
}
public function purchaseOrders()
{
return $this->hasMany(PurchaseOrder::class);
}

}