<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KaryawanDokumen extends Model
{
    protected $table = 'karyawan_dokumens';

    public const JENIS_OPTIONS = [
        'KTP'      => 'KTP (Kartu Tanda Penduduk)',
        'KK'       => 'Kartu Keluarga (KK)',
        'PKWT'     => 'Kontrak Kerja (PKWT)',
        'PKWTT'    => 'SK Pengangkatan Tetap (PKWTT)',
        'BPJS_KES' => 'Kartu BPJS Kesehatan',
        'BPJS_TK'  => 'Kartu BPJS Ketenagakerjaan',
        'NPWP'     => 'Kartu NPWP',
        'IJAZAH'   => 'Ijazah / Sertifikat Kompetensi',
        'LAINNYA'  => 'Dokumen Pendukung Lainnya',
    ];

    protected $fillable = [
        'karyawan_id',
        'jenis_dokumen',
        'nomor_dokumen',
        'tanggal_terbit',
        'tanggal_berlaku',
        'file_path',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_terbit'  => 'date',
        'tanggal_berlaku' => 'date',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
}
