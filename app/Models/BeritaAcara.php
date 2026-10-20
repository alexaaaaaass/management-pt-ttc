<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BeritaAcara extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'no_ba',
        'tipe',
        'tanggal',
        'no_surat_jalan_invoice',
        'customer_id',
        'item_product',
        'foto_bukti',
        'kronologi',
        'analisa_produksi',
        'tindakan_solusi',
        'peserta',
        'agenda',
        'catatan',
        'ttd_qc',
        'ttd_customer',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'foto_bukti' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public static function generateNoBA(): string
    {
        $prefix = 'BA/COMP/' . now()->format('Y') . '/';
        $lastSequence = static::withTrashed()
            ->where('no_ba', 'like', $prefix . '%')
            ->pluck('no_ba')
            ->map(fn (string $noBa) => (int) substr($noBa, strlen($prefix)))
            ->max() ?? 0;

        $sequence = $lastSequence + 1;

        return $prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }
}