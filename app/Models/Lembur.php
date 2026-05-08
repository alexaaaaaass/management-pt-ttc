<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Lembur extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_lembur',
        'karyawan_id',
        'kode_gudang',
        'tanggal_lembur',
        'jam_mulai',
        'jam_selesai',
        'durasi_lembur',
        'keterangan',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    protected static function booted()
    {
        static::creating(function ($data) {

            $date = now()->format('Ymd');
            $count = self::whereDate('created_at', now())->count() + 1;
            $urutan = str_pad($count, 3, '0', STR_PAD_LEFT);

            $data->kode_lembur = "LMB-{$date}-{$urutan}";
        });
    }
}