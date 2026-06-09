<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuti extends Model
{
<<<<<<< HEAD
    use HasFactory;

    protected $table = 'cuti';

    protected $fillable = [
        'karyawan_id',
        'tanggal_cuti',
=======
    protected $fillable = [
        'karyawan_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
>>>>>>> 2b7f9fc8bdc1b557da06ff9a81056e9442b7b258
        'jenis_cuti',
        'lampiran',
        'keterangan',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}