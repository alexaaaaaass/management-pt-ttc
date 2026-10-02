<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class SalesOrderItem extends Model
{
   protected $fillable = [

    'sales_order_id',

    'master_item_id',

    'qty',

    'harga',

    'subtotal',

];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function salesOrder()
    {
        return $this->belongsTo(
            SalesOrder::class
        );
    }

    public function suratJalanItems()
    {
        return $this->hasMany(
            SuratJalanItem::class
        );
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'itemable',
                'items.material',
                'customer',
            ]);
    }

    public function material()
    {
        return $this->belongsTo(
            MasterItem::class,
            'master_item_id'
        );
    }



}