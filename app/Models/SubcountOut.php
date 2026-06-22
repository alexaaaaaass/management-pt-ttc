<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;


class SubcountOut extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal_subcount',
        'supplier_id',
        'admin_produksi',
        'surat_jalan_subcount',
        'supervisor',
        'admin_mainstore',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_subcount' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(MasterSupplier::class);
    }

    public function items()
    {
        return $this->hasMany(SubcountOutItem::class);
    }

 protected static function booted()
{
    // Generate nomor surat jalan subcount
    static::creating(function ($model) {

        $bulanTahun = Carbon::now()->format('m-Y');

        $last = self::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->latest('id')
            ->first();

        if ($last && $last->surat_jalan_subcount) {

            $lastNumber = (int) substr(
                $last->surat_jalan_subcount,
                -6
            );

            $nextNumber = $lastNumber + 1;

        } else {

            $nextNumber = 1;
        }

        $model->surat_jalan_subcount =
            'SUB-OUT/' .
            $bulanTahun .
            '/' .
            str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    });


    // Update stock material
    static::created(function ($subcount) {

        foreach ($subcount->items as $item) {

            if ($item->sumber_item == 'material') {

                $stock = MaterialStock::where(
                    'item_id',
                    $item->item_id
                )->first();

                if ($stock) {

                    $stock->decrement(
                        'on_hand',
                        $item->qty
                    );

                    $stock->increment(
                        'allocation',
                        $item->qty
                    );
                }
            }
        }
    });
}

public function subcountInItems()
{
    return $this->hasMany(SubcountInItem::class);
}


}