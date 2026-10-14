<?php

namespace App\Models;

use App\Services\Finance\TreasuryService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransKasBank extends Model
{
    use HasFactory;

    protected $table = 'trans_kas_banks';

    protected $fillable = [
        'kode_transaksi',
        'tipe_transaksi',
        'gudang',
        'tanggal_transaksi',
        'periode',
        'account_bank_id',
        'account_lawan_id',
        'nominal',
        'customer_id',
        'bank',
        'atas_nama',
        'no_rekening',
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
                $record->kode_transaksi = app(TreasuryService::class)->generateKodeBank(
                    $record->tanggal_transaksi ? \Carbon\Carbon::parse($record->tanggal_transaksi) : null
                );
            }
        });
    }

    public function accountBank()
    {
        return $this->belongsTo(MasterCOA::class, 'account_bank_id');
    }

    public function accountLawan()
    {
        return $this->belongsTo(MasterCOA::class, 'account_lawan_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}