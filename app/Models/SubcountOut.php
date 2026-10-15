<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\Subcontract\SubcontractService;

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

    public function subcountInItems()
    {
        return $this->hasMany(SubcountInItem::class);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->surat_jalan_subcount) || $model->surat_jalan_subcount === 'AUTO') {
                $model->surat_jalan_subcount = app(SubcontractService::class)->generateSubcountOutNumber();
            }
        });

        static::deleting(function ($model) {
            if ($model->subcountInItems()->exists()) {
                throw new \Exception('Tidak dapat menghapus Subcount Out karena sudah memiliki catatan penerimaan (Subcount In).');
            }
        });
    }
}