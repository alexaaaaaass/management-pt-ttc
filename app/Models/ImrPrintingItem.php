<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImrPrintingItem extends Model
{
    protected $table = 'imr_printing_items';

    protected $fillable = [

        'imr_id',

        'item_id',

        'department',

        'catatan',

        'satuan',

        'total_pesanan',

        'qty_request',

        'qty_approved',

        'qty_issued',

        'qty_remaining',

    ];

 protected static function booted()
{
    static::saving(function ($item) {

        $item->qty_remaining = max(
            0,
            $item->qty_approved - $item->qty_issued
        );

    });

    static::saved(function ($item) {

        $imr = $item->imr;

        if (!$imr) {
            return;
        }

        $request = $imr->items()->sum('qty_request');
        $approved = $imr->items()->sum('qty_approved');

        if ($approved == 0) {

            // kalau user memang reject
            if ($imr->status == 'Rejected') {
                return;
            }

            $status = 'Pending';

        } elseif ($approved < $request) {

            $status = 'Partial';

        } else {

            $status = 'Approved';

        }

        $imr->updateQuietly([
            'status' => $status,
        ]);
    });
}

    public function imr()
    {
        return $this->belongsTo(ImrPrinting::class);
    }

    public function item()
    {
        return $this->belongsTo(
            MasterItem::class,
            'item_id'
        );
    }
}