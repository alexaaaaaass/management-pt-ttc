<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'karyawans';

    public const TIPE_GAJI_OPTIONS = [
        'Bulanan'   => 'Bulanan (Tetap / Kontrak)',
        'Harian'    => 'Harian (Per Hari Hadir)',
        'Freelance' => 'Freelance (Lepas / Proyek)',
        'Borongan'  => 'Borongan / Part-Time',
    ];

    protected $fillable = [
        'nama',
        'nip',
        'pin_finger',
        'tempat_lahir',
        'tanggal_lahir',
        'departemen_id',
        'shift_id',
        'jabatan',
        'kantor',
        'tanggal_masuk_kerja',
        'tangal_akhir_kontrak',
        'no_telp',
        'nik',
        'no_ktp',
        'status_pegawai',
        'jenis_kelamin',
        'agama',

        'gaji_pokok',
        'tipe_gaji',
        'is_borongan',
        'tunjangan_kompetensi',
        'tunjangan_jabatan',
        'tunjangan_intensif',
        'tunjangan_makan',
        'tunjangan_hadir',
        'tunjangan_lain',
        'tarif_lembur_custom',

        'nama_npwp',
        'nomor_npwp',
        'alamat_npwp',
        'tanggal_npwp',
        'PTKP',

        'nama_bank',
        'rekening_atas_nama',
        'no_rekening',

        'nama_bpjs',
        'bpjs_kesehatan',
        'bpjs_ketenagakerjaan',
        'bpjs_kesehatan_nominal',
        'bpjs_ketenagakerjaan_nominal',
    ];

    protected $casts = [
        'tanggal_lahir'                => 'date',
        'tanggal_masuk_kerja'          => 'date',
        'tangal_akhir_kontrak'         => 'date',
        'tanggal_npwp'                 => 'date',
        'gaji_pokok'                   => 'float',
        'tunjangan_kompetensi'         => 'float',
        'tunjangan_jabatan'            => 'float',
        'tunjangan_intensif'           => 'float',
        'tunjangan_makan'              => 'float',
        'tunjangan_hadir'              => 'float',
        'tunjangan_lain'               => 'float',
        'tarif_lembur_custom'          => 'float',
        'bpjs_kesehatan_nominal'       => 'float',
        'bpjs_ketenagakerjaan_nominal' => 'float',
        'is_borongan'                  => 'boolean',
    ];

    public function departemen()
    {
        return $this->belongsTo(MasterDepartemen::class, 'departemen_id');
    }

    public function shift()
    {
        return $this->belongsTo(HrShift::class, 'shift_id');
    }

    public function dokumens()
    {
        return $this->hasMany(KaryawanDokumen::class, 'karyawan_id')->orderByDesc('id');
    }

    public function disiplins()
    {
        return $this->hasMany(KaryawanDisiplin::class, 'karyawan_id')->orderByDesc('tanggal_surat');
    }

    public function gajis()
    {
        return $this->hasMany(Gaji::class, 'karyawan_id');
    }

    public function lemburs()
    {
        return $this->hasMany(Lembur::class, 'karyawan_id');
    }

    public function cutis()
    {
        return $this->hasMany(Cuti::class, 'karyawan_id');
    }

    public function izins()
    {
        return $this->hasMany(Izin::class, 'karyawan_id');
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class, 'karyawan_id');
    }

    public function bonuses()
    {
        return $this->hasMany(BonusKaryawan::class, 'karyawan_id');
    }

    public function potonganTunjangans()
    {
        return $this->hasMany(PotTunjangan::class, 'karyawan_id');
    }

    public function pinjamans()
    {
        return $this->hasMany(PengPinjaman::class, 'karyawan_id');
    }

    public function coas()
    {
        return $this->hasMany(MasterCOA::class, 'karyawan_id');
    }

    public function coaClasses()
    {
        return $this->hasMany(CoaClass::class, 'karyawan_id');
    }
}