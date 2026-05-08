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
protected static function booted()
{
    static::creating(function ($pr) {

        $tanggal = $pr->tanggal_pr ?? now();

        $date = \Carbon\Carbon::parse($tanggal)->format('Ymd');

        $count = self::whereDate('tanggal_pr', $tanggal)->count() + 1;

        $sequence = str_pad($count, 3, '0', STR_PAD_LEFT);

        $pr->nomor_pr = "PR-{$date}-{$sequence}";

        $pr->status = 'deotorisasi'; // default
    });
}
}