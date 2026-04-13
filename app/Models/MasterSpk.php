<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MasterSpk extends Model
{
    protected $table = 'master_spk';

   protected $fillable = [
    'no_spk',
    'kode_ik',
    'sales_order_id',
    'production_plan',
    'status',
    'tanggal_estimasi_selesai',
    'tanggal_po',
];

    // 🔥 RELASI
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }



    // 🔥 AUTO GENERATE NO SPK
   protected static function booted()
{
    static::creating(function ($model) {

        $so = SalesOrder::find($model->sales_order_id);

        if ($so) {
            $model->tanggal_po = $so->tanggal_po;
        }

        $count = self::count() + 1;
        $bulanTahun = Carbon::now()->format('mY');

        $model->no_spk = str_pad($count, 4, '0', STR_PAD_LEFT)
            . '/SPK-IK' . $model->kode_ik
            . '/' . $bulanTahun;
    });
}

    
}