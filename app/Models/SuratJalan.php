<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratJalan extends Model
{
    use HasFactory;

    protected $fillable = [

        'kode_surat_jalan',

        'sales_order_id',

        'spk_id',

        // Khusus SPK
        'qty_pengiriman',

        'tanggal_surat_jalan',

        'alamat_tujuan',

        'transportasi',

        'no_polisi',

        'driver',

        'pengirim',

        'keterangan',

    ];

    public function salesOrder()
    {
        return $this->belongsTo(
            SalesOrder::class
        );
    }

    public function spk()
    {
        return $this->belongsTo(
            MasterSpk::class,
            'spk_id'
        );
    }

    public function invoice()
    {
        return $this->hasOne(
            Invoice::class
        );
    }

    public function items()
    {
        return $this->hasMany(
            SuratJalanItem::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PROCESS SPK
    |--------------------------------------------------------------------------
    */

    protected static function processSpk(
        SuratJalan $suratJalan
    ): void {

        $spk = $suratJalan->spk;

        if (! $spk) {
            return;
        }

        $spk->decrement(
            'on_hand_stock',
            $suratJalan->qty_pengiriman
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO NUMBER
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($model) {

            $tanggal = Carbon::now()->format('Ymd');

            $count = self::whereDate(
                'created_at',
                today()
            )->count() + 1;

            $model->kode_surat_jalan =
                'SJ-'
                . $tanggal
                . '-'
                . str_pad(
                    $count,
                    3,
                    '0',
                    STR_PAD_LEFT
                );
        });

        /*
        |--------------------------------------------------------------------------
        | Setelah Surat Jalan dibuat
        |--------------------------------------------------------------------------
        |
        | Hanya SPK yang diproses di Model.
        | Material diproses di CreateSuratJalan
        | agar SuratJalanItem sudah tersedia.
        |
        */

        static::created(function (SuratJalan $suratJalan) {

            $so = $suratJalan->salesOrder;

            if (! $so) {
                return;
            }

            if ($so->order_type === 'SPK') {

                self::processSpk(
                    $suratJalan
                );

            }

        });
    }
}