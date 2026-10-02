<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratJalanItem extends Model
{
    protected $fillable = [

        'surat_jalan_id',

        'sales_order_item_id',

        'master_item_id',

        'qty_kirim',

    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function suratJalan()
    {
        return $this->belongsTo(
            SuratJalan::class
        );
    }

    public function salesOrderItem()
    {
        return $this->belongsTo(
            SalesOrderItem::class
        );
    }

    public function material()
    {
        return $this->belongsTo(
            MasterItem::class,
            'master_item_id'
        );
    }
}