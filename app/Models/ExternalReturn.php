<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\PenerimaanBarang;
use App\Models\ExternalReturnItem;


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

    public function penerimaanBarang()
    {
        return $this->belongsTo(PenerimaanBarang::class);
    }

    public function items()
    {
        return $this->hasMany(ExternalReturnItem::class);
    }

    protected static function booted()
    {
        static::creating(function ($data) {
            $date = now()->format('Ymd');
            $count = self::count() + 1;
            $data->no_return = "RET-{$date}-" . str_pad($count, 3, '0', STR_PAD_LEFT);
        });
    }
}