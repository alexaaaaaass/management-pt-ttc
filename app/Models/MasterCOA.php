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

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function coaClass()
    {
        return $this->belongsTo(CoaClass::class);
    }
}