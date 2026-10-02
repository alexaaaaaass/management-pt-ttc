<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PenerimaanBarang;

class ExternalReturn extends Model
{
    protected $fillable = [

        'no_return',

        'penerimaan_barang_id',

        'tanggal_return',

        'supplier_id',

        'alasan',

        'status',

    ];

   protected static function booted()
{
    parent::booted();

    static::creating(function ($return) {

        $date = $return->tanggal_return
            ? \Carbon\Carbon::parse($return->tanggal_return)
            : now();

        $count = self::whereDate(
            'tanggal_return',
            $date
        )->count() + 1;

        $return->no_return =
            'RET-'
            .$date->format('Ymd')
            .'-'
            .str_pad($count,3,'0',STR_PAD_LEFT);

        if (!$return->status) {
            $return->status = 'draft';
        }

        if ($return->penerimaan_barang_id) {

            $pb = PenerimaanBarang::with('purchaseOrder')
                ->find($return->penerimaan_barang_id);

            if ($pb && $pb->purchaseOrder) {

                $return->supplier_id =
                    $pb->purchaseOrder->supplier_id;

            }

        }

    });
}
    public function penerimaanBarang()
    {
        return $this->belongsTo(PenerimaanBarang::class);
    }

    public function supplier()
    {
        return $this->belongsTo(MasterSupplier::class);
    }

    public function items()
    {
        return $this->hasMany(ExternalReturnItem::class);
    }
}