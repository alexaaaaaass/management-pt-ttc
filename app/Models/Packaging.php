<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Packaging extends Model
{
    use HasFactory;

    protected $fillable = [

        'kode_packaging',

        'spk_id',

        'satuan_transfer',
        'jenis_transfer',
        'tgl_transfer',

        'jumlah_satuan_penuh',
        'qty_per_satuan_penuh',

        'jumlah_satuan_sisa',
        'qty_per_satuan_sisa',

        'total_satuan_penuh',
        'total_satuan_sisa',
        'grand_total',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function spk()
    {
        return $this->belongsTo(MasterSpk::class, 'spk_id');
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO GENERATE + HITUNG TOTAL
    |--------------------------------------------------------------------------
    */

   protected static function booted()
{
    static::creating(function ($model) {

        $tanggal = now()->format('Ymd');

        // hitung total data hari ini
        $count = self::count() + 1;

        $model->kode_packaging =
            'PKG-' .
            $tanggal .
            '-' .
            str_pad($count, 3, '0', STR_PAD_LEFT);
    });

    static::saving(function ($model) {

        $model->total_satuan_penuh =
            (int) $model->jumlah_satuan_penuh
            * (int) $model->qty_per_satuan_penuh;

        $model->total_satuan_sisa =
            (int) $model->jumlah_satuan_sisa
            * (int) $model->qty_per_satuan_sisa;

        $model->grand_total =
            $model->total_satuan_penuh
            + $model->total_satuan_sisa;
    });
}
}