<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoaClass extends Model
{
    protected $fillable = [
        'karyawan_id',
        'code',
        'name',
        'status',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
    public function masterCoas()
{
    return $this->hasMany(MasterCOA::class, 'coa_class_id');
}
}