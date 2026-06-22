<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubcountInItem extends Model
{
    protected $fillable = [
        'subcount_in_id',
        'subcount_out_id',
        'item_asal_id',
        'item_hasil_id',
        'qty_dikirim',
        'qty_diterima',
        'keterangan',
    ];

    public function subcountIn()
    {
        return $this->belongsTo(SubcountIn::class);
    }

    public function subcountOut()
    {
        return $this->belongsTo(SubcountOut::class);
    }

    public function itemHasil()
    {
        return $this->belongsTo(
            MasterItem::class,
            'item_hasil_id'
        );
    }

    protected static function booted()
    {
        static::created(function ($itemIn) {

            /*
            |--------------------------------------------------------------------------
            | Tambah stok material hasil
            |--------------------------------------------------------------------------
            */

            $stock = MaterialStock::firstOrCreate(
                [
                    'item_id' => $itemIn->item_hasil_id
                ],
                [
                    'on_hand' => 0,
                    'allocation' => 0
                ]
            );

            $stock->increment(
                'on_hand',
                $itemIn->qty_diterima
            );


            /*
            |--------------------------------------------------------------------------
            | Kurangi allocation material asal
            |--------------------------------------------------------------------------
            */

            $rawStock = MaterialStock::where(
                'item_id',
                $itemIn->item_asal_id
            )->first();

            if ($rawStock) {

                $rawStock->decrement(
                    'allocation',
                    $itemIn->qty_diterima
                );
            }
        });
    }
}