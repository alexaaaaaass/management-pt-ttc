<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\Subcontract\SubcontractService;

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

    protected $casts = [
        'tanggal_subcount' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(SubcountInItem::class);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->no_subcount) || $model->no_subcount === 'AUTO') {
                $model->no_subcount = app(SubcontractService::class)->generateSubcountInNumber();
            }
        });
    }
}