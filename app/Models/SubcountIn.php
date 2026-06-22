<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SubcountIn extends Model
{
    use HasFactory;

   protected $fillable = [
    'no_subcount',
    'tanggal_subcount',
    'surat_jalan_pengiriman',
    'admin_produksi',
    'supervisor',
    'admin_mainstore',
    'keterangan',
];

    public function items()
    {
        return $this->hasMany(SubcountInItem::class);
    }

  protected static function booted()
{
    static::creating(function ($model) {

        $bulanTahun = Carbon::now()->format('m-Y');

        $last = self::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->latest('id')
            ->first();

        if ($last) {

            $lastNumber = (int) substr(
                $last->no_subcount,
                -6
            );

            $nextNumber = $lastNumber + 1;

        } else {

            $nextNumber = 1;
        }

        $model->no_subcount =
            'SUB-IN/' .
            $bulanTahun .
            '/' .
            str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    });
}
}