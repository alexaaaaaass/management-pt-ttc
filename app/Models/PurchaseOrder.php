<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'supplier_id',
        'po_date',
        'eta',
        'currency',
        'ppn',
        'ongkir',
        'dp'
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function supplier()
    {
        return $this->belongsTo(MasterSupplier::class,'supplier_id');
    }
}