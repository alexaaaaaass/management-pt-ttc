<?php

namespace App\Models;

use App\Services\Finance\NpbService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpbRequest extends Model
{
    use HasFactory;

    protected $table = 'npb_requests';

    public const KATEGORI_OPTIONS = [
        'A_KEWAJIBAN_TETAP'        => 'A. KEWAJIBAN TETAP PERUSAHAAN',
        'B_UTILITAS_OPERASIONAL'   => 'B. UTILITAS DAN OPERASIONAL',
        'C_PRODUKSI_FASILITAS'     => 'C. KEBUTUHAN PRODUKSI DAN FASILITAS',
        'D_PENGADAAN_PENGURUSAN'   => 'D. PENGADAAN DAN PENGURUSAN',
        'E_ADMINISTRASI_LEGALITAS' => 'E. ADMINISTRASI DAN LEGALITAS',
        'F_LAINNYA'                => 'F. KEBUTUHAN LAIN-LAIN',
    ];

    public const STATUS_OPTIONS = [
        'DRAFT'           => 'Draft',
        'SUBMITTED'       => 'Diajukan',
        'REVIEWED'        => 'Diketahui Kepala',
        'APPROVED'        => 'Disetujui (Approved)',
        'DISBURSED'       => 'Dana Dicairkan',
        'PARTIAL_SETTLED' => 'LPJ Sebagian',
        'SETTLED'         => 'Selesai (Closed)',
        'REJECTED'        => 'Ditolak',
    ];

    public const SUMBER_DANA_OPTIONS = [
        'MODAL_PEMILIK'     => 'Setoran Modal Pemilik (COA 3100)',
        'HUTANG_PEMILIK'    => 'Hutang / Pinjaman Pemilik (COA 2142)',
        'PINJAMAN_INDIGAMA' => 'Pinjaman / Talangan PT Indigama (COA 2144)',
        'DANA_INTERNAL'     => 'Dana Internal / Saldo Rekening TTC (Tanpa Tambah Modal)',
    ];

    public const DEFAULT_CATATAN = "1. Nilai bulan berjalan merupakan kebutuhan periode berjalan; nilai bulan berikutnya merupakan proyeksi dan dapat disesuaikan dengan realisasi.\n2. Pos insidental dibayarkan sesuai jadwal/tagihan dan tidak otomatis berulang setiap bulan.\n3. Lampirkan rincian tagihan, dasar perhitungan, dan bukti pendukung pada saat pengajuan/pencairan.";

    protected $fillable = [
        'nomor_pengajuan',
        'tanggal_pengajuan',
        'departemen_kode',
        'perihal',
        'periode_utama',
        'periode_proyeksi',
        'total_kebutuhan_utama',
        'total_kebutuhan_proyeksi',
        'total_kebutuhan_gabungan',
        'total_saldo_sebelumnya',
        'total_dana_tersedia',
        'kekurangan_pendanaan',
        'catatan',
        'status',
        'disusun_oleh',
        'mengetahui_nama',
        'mengetahui_at',
        'diajukan_kepada',
        'disetujui_oleh',
        'disetujui_at',
        'catatan_approval',
        'tanggal_pencairan',
        'nominal_dicairkan',
        'sumber_dana_pencairan',
        'coa_tujuan_pencairan_id',
        'is_pencairan_posted',
        'created_by',
    ];

    protected $casts = [
        'tanggal_pengajuan'        => 'date',
        'tanggal_pencairan'        => 'date',
        'mengetahui_at'            => 'datetime',
        'disetujui_at'             => 'datetime',
        'total_kebutuhan_utama'    => 'float',
        'total_kebutuhan_proyeksi' => 'float',
        'total_kebutuhan_gabungan' => 'float',
        'total_saldo_sebelumnya'   => 'float',
        'total_dana_tersedia'      => 'float',
        'kekurangan_pendanaan'     => 'float',
        'nominal_dicairkan'        => 'float',
        'is_pencairan_posted'      => 'boolean',
    ];

    public function fundPositions()
    {
        return $this->hasMany(NpbFundPosition::class, 'npb_request_id')->orderBy('urutan')->orderBy('id');
    }

    public function items()
    {
        return $this->hasMany(NpbItem::class, 'npb_request_id')->orderBy('kategori')->orderBy('urutan')->orderBy('id');
    }

    public function settlements()
    {
        return $this->hasMany(NpbSettlement::class, 'npb_request_id')->orderByDesc('tanggal_penyelesaian');
    }

    public function coaTujuanPencairan()
    {
        return $this->belongsTo(MasterCOA::class, 'coa_tujuan_pencairan_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getIsApprovedStatusAttribute(): bool
    {
        return in_array($this->status, ['APPROVED', 'DISBURSED', 'PARTIAL_SETTLED', 'SETTLED'], true);
    }

    protected static function booted(): void
    {
        static::creating(function (NpbRequest $model) {
            if (empty($model->nomor_pengajuan) || strtoupper(trim($model->nomor_pengajuan)) === 'AUTO') {
                $model->nomor_pengajuan = app(NpbService::class)->generateNomorNpb(
                    $model->tanggal_pengajuan ? Carbon::parse($model->tanggal_pengajuan) : now(),
                    $model->departemen_kode ?: 'KEU'
                );
            }
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }
}
