<?php

namespace App\Models;

use App\Services\Subcontract\SubcontractService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubcountOut extends Model
{
    use HasFactory;

    public const JENIS_PROSES_OPTIONS = [
        'FULL_MAKLON'   => 'Full Maklon / Produksi Penuh',
        'PRINTING'      => 'Printing / Cetak',
        'LAMINASI'      => 'Laminasi (Doff / Glossy)',
        'UV_SPOT'       => 'UV / Spot UV / Varnish',
        'HOTPRINT_FOIL' => 'Hotprint / Foil Stamping',
        'PON_DIECUT'    => 'Pon / Die-Cut / Emboss',
        'LEM_LIPAT'     => 'Lem / Lipat / Finishing',
        'LAINNYA'       => 'Proses Lainnya',
    ];

    protected $fillable = [
        'tanggal_subcount',
        'tanggal_target_selesai',
        'supplier_id',
        'jenis_proses',
        'admin_produksi',
        'surat_jalan_subcount',
        'supervisor',
        'admin_mainstore',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_subcount' => 'date',
        'tanggal_target_selesai' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(MasterSupplier::class, 'supplier_id');
    }

    public function items()
    {
        return $this->hasMany(SubcountOutItem::class);
    }

    public function subcountIns()
    {
        return $this->hasMany(SubcountIn::class, 'subcount_out_id');
    }

    public function subcountInItems()
    {
        return $this->hasMany(SubcountInItem::class);
    }

    public function getTotalSentQtyAttribute(): float
    {
        if ($this->relationLoaded('items')) {
            return (float) $this->items->sum('qty');
        }

        return (float) $this->items()->sum('qty');
    }

    public function getTotalReceivedQtyAttribute(): float
    {
        if ($this->relationLoaded('subcountInItems')) {
            return (float) $this->subcountInItems->sum('qty_diterima');
        }

        return (float) $this->subcountInItems()->sum('qty_diterima');
    }

    public function getOutstandingQtyAttribute(): float
    {
        return max(0.0, round($this->total_sent_qty - $this->total_received_qty, 2));
    }

    public function getStatusPenerimaanAttribute(): string
    {
        $sent = $this->total_sent_qty;
        $received = $this->total_received_qty;

        if ($sent > 0 && $received >= $sent) {
            return 'SELESAI';
        }

        if ($received > 0) {
            return 'PARTIAL';
        }

        return 'DI_VENDOR';
    }

    public function getJenisProsesLabelAttribute(): string
    {
        return self::JENIS_PROSES_OPTIONS[$this->jenis_proses] ?? ($this->jenis_proses ?: '-');
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
