<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterProsesProduksi extends Model
{
    protected $table = 'master_proses_produksi';

    public const STASIUN_PRINTING = 'printing';

    public const STASIUN_DIEMAKING = 'diemaking';

    public const STASIUN_FINISHING = 'finishing';

    protected $fillable = [
        'kode_proses',
        'stasiun',
        'nama_proses',
        'urutan',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'urutan'    => 'integer',
        'is_active' => 'boolean',
    ];

    public static function stasiunOptions(): array
    {
        return [
            self::STASIUN_PRINTING  => 'Printing',
            self::STASIUN_DIEMAKING => 'Die Making',
            self::STASIUN_FINISHING => 'Finishing',
        ];
    }

    /**
     * Ambil pilihan proses aktif untuk stasiun tertentu [nama_proses => nama_proses].
     */
    public static function optionsFor(string $stasiun): array
    {
        return static::query()
            ->where('stasiun', $stasiun)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('nama_proses')
            ->pluck('nama_proses', 'nama_proses')
            ->all();
    }

    /**
     * Saran kode proses otomatis per stasiun (PRT-001, DMK-001, FIN-001).
     */
    public static function suggestKode(string $stasiun): string
    {
        $prefix = match ($stasiun) {
            self::STASIUN_PRINTING  => 'PRT-',
            self::STASIUN_DIEMAKING => 'DMK-',
            self::STASIUN_FINISHING => 'FIN-',
            default                 => 'PRS-',
        };

        $last = static::where('kode_proses', 'like', $prefix . '%')
            ->orderByDesc('kode_proses')
            ->value('kode_proses');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
