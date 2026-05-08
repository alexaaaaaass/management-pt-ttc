<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ExternalReturnItem extends Model
{
    protected $fillable = [
        'external_return_id',
        'penerimaan_barang_item_id',
        'qty_return',
        'catatan',
    ];

    public function externalReturn()
    {
        return $this->belongsTo(ExternalReturn::class);
    }

    public function penerimaanBarangItem()
    {
        return $this->belongsTo(PenerimaanBarangItem::class);
    }
}