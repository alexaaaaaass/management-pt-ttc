<?php

namespace App\Models;

use App\Services\Finance\NpbService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpbSettlement extends Model
{
    use HasFactory;

    protected $table = 'npb_settlements';

    public const STATUS_OPTIONS = [
        'DRAFT'     => 'Draft LPJ',
        'SUBMITTED' => 'Diajukan Verifikasi',
        'VERIFIED'  => 'Terverifikasi Kepala',
        'APPROVED'  => 'Disetujui & Selesai',
    ];

    public const PERIODE_OPTIONS = [
        'UTAMA'    => 'Periode Utama (Bulan Berjalan)',
        'PROYEKSI' => 'Periode Proyeksi (Bulan Berikutnya)',
        'GABUNGAN' => 'Gabungan 2 Periode',
    ];

    public const TINDAK_LANJUT_OPTIONS = [
        'CARRY_OVER'       => 'Sisa Dana Dibawa ke NPB Bulan Berikutnya (Carry-Over)',
        'KEMBALIKAN_KAS'   => 'Sisa Dana Dikembalikan ke Rekening / Owner',
        'REIMBURSE_KURANG' => 'Kekurangan Dana Diajukan Reimburse / Tambahan',
        'NIHIL'            => 'Nihil / Sesuai Dana Dicairkan',
    ];

    public const POSTING_MODE_OPTIONS = [
        'MANUAL_LINK_ONLY' => 'Dokumen LPJ / Tracking Saja (Mutasi Bank Diinput Terpisah — Anti Dobel)',
        'AUTO_JOURNAL'     => 'Auto-Posting ke Buku Besar saat Disetujui (Bank/Kas & 2144 Indigama)',
    ];

    protected $fillable = [
        'nomor_penyelesaian',
        'npb_request_id',
        'tanggal_penyelesaian',
        'periode_realisasi',
        'label_periode',
        'total_anggaran',
        'total_dana_dicairkan',
        'total_realisasi',
        'selisih_anggaran',
        'sisa_dana',
        'tindak_lanjut_sisa',
        'posting_mode',
        'is_posted_to_ledger',
        'status',
        'catatan',
        'disusun_oleh',
        'diperiksa_oleh',
        'disetujui_oleh',
        'disetujui_at',
        'created_by',
    ];

    protected $casts = [
        'tanggal_penyelesaian' => 'date',
        'disetujui_at'         => 'datetime',
        'total_anggaran'       => 'float',
        'total_dana_dicairkan' => 'float',
        'total_realisasi'      => 'float',
        'selisih_anggaran'     => 'float',
        'sisa_dana'            => 'float',
        'is_posted_to_ledger'  => 'boolean',
    ];

    public function npbRequest()
    {
        return $this->belongsTo(NpbRequest::class, 'npb_request_id');
    }

    public function items()
    {
        return $this->hasMany(NpbSettlementItem::class, 'npb_settlement_id')->orderBy('kategori')->orderBy('urutan')->orderBy('id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted(): void
    {
        static::creating(function (NpbSettlement $model) {
            if (empty($model->nomor_penyelesaian) || strtoupper(trim($model->nomor_penyelesaian)) === 'AUTO') {
                $dept = $model->npbRequest?->departemen_kode ?: 'KEU';
                $model->nomor_penyelesaian = app(NpbService::class)->generateNomorSettlement(
                    $model->tanggal_penyelesaian ? Carbon::parse($model->tanggal_penyelesaian) : now(),
                    $dept
                );
            }
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }
}
