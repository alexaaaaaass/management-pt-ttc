<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PracetakMaterialMovement extends Model
{
    protected $table = 'pracetak_material_movements';

    protected $fillable = [
        'master_material_plate_id',
        'tanggal',
        'nomor_transaksi',
        'jenis_transaksi',
        'referensi_type',
        'referensi_id',
        'qty_masuk',
        'qty_keluar',
        'saldo_sebelum',
        'saldo_setelah',
        'harga_satuan',
        'total_nominal',
        'supplier_id',
        'user_id',
        'catatan',
    ];

    protected $casts = [
        'tanggal'       => 'date',
        'qty_masuk'     => 'decimal:2',
        'qty_keluar'    => 'decimal:2',
        'saldo_sebelum' => 'decimal:2',
        'saldo_setelah' => 'decimal:2',
        'harga_satuan'  => 'decimal:2',
        'total_nominal' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function plate(): BelongsTo
    {
        return $this->belongsTo(MasterMaterialPlate::class, 'master_material_plate_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(MasterSupplier::class, 'supplier_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function referensi(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'referensi_type', 'referensi_id');
    }
}
