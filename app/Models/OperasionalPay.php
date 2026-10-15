<?php

namespace App\Models;

use App\Services\Finance\TreasuryService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperasionalPay extends Model
{
    use HasFactory;

    protected $table = 'operasional_pays';

    protected $fillable = [
        'kode_transaksi',
        'gudang',
        'periode',
        'tanggal_transaksi',
        'account_kas_id',
        'account_beban_id',
        'karyawan_id',
        'nominal',
        'nopol',
        'odometer',
        'mesin',
        'jenis',
        'kode',
        'keterangan',
        'status',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'nominal'           => 'float',
    ];

    protected static function booted()
    {
        static::creating(function ($record) {
            if (empty($record->kode_transaksi) || $record->kode_transaksi === 'AUTO') {
                $record->kode_transaksi = app(TreasuryService::class)->generateKodeOperasional(
                    $record->tanggal_transaksi ? \Carbon\Carbon::parse($record->tanggal_transaksi) : null
                );
            }
        });
    }

    public function accountKas()
    {
        return $this->belongsTo(MasterCOA::class, 'account_kas_id');
    }

    public function accountBeban()
    {
        return $this->belongsTo(MasterCOA::class, 'account_beban_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}