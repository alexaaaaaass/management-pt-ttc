<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterCOA extends Model
{
    use HasFactory;

    protected $table = 'master_coas';

    protected $fillable = [
        'karyawan_id',
        'coa_class_id',
        'periode',
        'gudang',
        'kode_akun',
        'nama_akun',
        'saldo_debit',
        'saldo_kredit',
        'nominal_default',
        'keterangan',
        'status',
    ];

    protected $casts = [
        'saldo_debit'     => 'float',
        'saldo_kredit'    => 'float',
        'nominal_default' => 'float',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function coaClass()
    {
        return $this->belongsTo(CoaClass::class);
    }

    public function movements()
    {
        return $this->hasMany(CoaMovement::class, 'coa_id');
    }

    /**
     * Saldo Awal (Debit - Kredit)
     */
    public function getSaldoAwalAttribute(): float
    {
        return (float) ($this->saldo_debit - $this->saldo_kredit);
    }

    /**
     * Saldo Berjalan (Current Balance)
     * Saldo Awal + Mutasi yang tercatat di coa_movements
     */
    public function getSaldoBerjalanAttribute(): float
    {
        $lastMovement = $this->movements()->orderByDesc('tanggal')->orderByDesc('id')->first();
        if ($lastMovement) {
            return (float) $lastMovement->balance_after;
        }

        return $this->saldo_awal;
    }
}