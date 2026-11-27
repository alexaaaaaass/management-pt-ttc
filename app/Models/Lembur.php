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
        'status',
        'approved_by',
        'approved_at',
        'catatan_approval',
    ];

    protected $casts = [
        'tanggal_lembur' => 'date',
        'durasi_lembur'  => 'float',
        'approved_at'    => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    protected static function booted()
    {
        static::creating(function ($data) {
            if (empty($data->kode_lembur) || strtoupper(trim($data->kode_lembur)) === 'AUTO') {
                $date = now()->format('Ymd');
                $prefix = "LMB-{$date}-";
                $seq = 1;
                $last = self::where('kode_lembur', 'like', "{$prefix}%")->orderByDesc('id')->value('kode_lembur');
                if ($last && preg_match('/-(\d+)$/', $last, $m)) {
                    $seq = ((int) $m[1]) + 1;
                }
                while (self::where('kode_lembur', $prefix . str_pad((string) $seq, 3, '0', STR_PAD_LEFT))->exists()) {
                    $seq++;
                }
                $data->kode_lembur = $prefix . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            }
            if (empty($data->status)) {
                $data->status = 'Pending';
            }
        });
    }
}