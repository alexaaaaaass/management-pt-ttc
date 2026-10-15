<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'karyawans';

    protected $fillable = [
        'nama',
        'nip',
        'tempat_lahir',
        'tanggal_lahir',
        'departemen_id',
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
        'tunjangan_kompetensi',
        'tunjangan_jabatan',
        'tunjangan_intensif',

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
    ];

    protected $casts = [
        'tanggal_lahir'         => 'date',
        'tanggal_masuk_kerja'   => 'date',
        'tangal_akhir_kontrak'  => 'date',
        'gaji_pokok'            => 'float',
        'tunjangan_kompetensi'  => 'float',
        'tunjangan_jabatan'     => 'float',
        'tunjangan_intensif'    => 'float',
    ];

    public function departemen()
    {
        return $this->belongsTo(MasterDepartemen::class, 'departemen_id');
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