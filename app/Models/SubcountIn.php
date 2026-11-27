<?php

namespace App\Models;

use App\Services\Subcontract\SubcontractService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubcountIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_subcount',
        'subcount_out_id',
        'supplier_id',
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

    public function subcountOut()
    {
        return $this->belongsTo(SubcountOut::class, 'subcount_out_id');
    }

    public function supplier()
    {
        return $this->belongsTo(MasterSupplier::class, 'supplier_id');
    }

    public function items()
    {
        return $this->hasMany(SubcountInItem::class);
    }

    public function getResolvedSubcountOutAttribute(): ?SubcountOut
    {
        return $this->subcountOut ?? $this->items->first()?->subcountOut;
    }

    public function getResolvedSupplierAttribute(): ?MasterSupplier
    {
        return $this->supplier ?? $this->resolved_subcount_out?->supplier;
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
