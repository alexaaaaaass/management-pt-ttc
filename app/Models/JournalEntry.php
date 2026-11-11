<?php

namespace App\Models;

use App\Services\Finance\TreasuryService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $table = 'journal_entries';

    protected $fillable = [
        'kode_jurnal',
        'tanggal',
        'periode',
        'gudang',
        'jenis_jurnal',
        'no_referensi',
        'karyawan_id',
        'keterangan',
        'total_debit',
        'total_kredit',
        'status',
        'created_by',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'total_debit'  => 'float',
        'total_kredit' => 'float',
    ];

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getIsBalancedAttribute(): bool
    {
        return abs((float) $this->total_debit - (float) $this->total_kredit) < 0.01 && (float) $this->total_debit > 0;
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode_jurnal) || $model->kode_jurnal === 'AUTO') {
                $model->kode_jurnal = app(TreasuryService::class)->generateKodeJurnal(
                    $model->tanggal ? Carbon::parse($model->tanggal) : null
                );
            }
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }
}

