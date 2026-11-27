<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrPayrollSetting extends Model
{
    protected $table = 'hr_payroll_settings';

    protected $fillable = [
        'jam_masuk_standar',
        'jam_pulang_standar',
        'toleransi_telat_menit',
        'kuota_cuti_tahunan',
        'pakai_cutoff_bulanan',
        'cutoff_tanggal_mulai',
        'cutoff_tanggal_selesai',
        'metode_lembur',
        'tarif_lembur_tetap_per_jam',
        'pengali_lembur_hari_kerja',
        'pengali_lembur_hari_libur',
        'uang_makan_lembur_nominal',
        'min_jam_uang_makan_lembur',
        'potongan_telat_per_kali',
        'metode_potongan_telat',
        'potongan_alpha_per_hari',
        'potongan_sakit_tanpa_surat',
        'persen_bpjs_kesehatan',
        'persen_bpjs_ketenagakerjaan',
        'persen_bpjs_tk_perusahaan',
        'persen_bpjs_kes_perusahaan',
        'auto_hitung_bpjs',
        'tarif_protol_default',
        'tarif_packing_default',
        'tarif_lain_borongan_default',
        'tunj_hadir_borongan_default',
        'tunj_makan_borongan_default',
        'default_account_kas_id',
        'coa_beban_gaji_umum_id',
        'coa_beban_gaji_produksi_id',
        'coa_beban_gaji_sales_id',
        'coa_beban_gaji_admin_id',
        'coa_beban_bpjs_id',
        'coa_piutang_karyawan_id',
        'coa_hutang_gaji_id',
        'coa_hutang_bpjs_id',
        'coa_hutang_pph21_id',
        'coa_hutang_karyawan_lain_id',
    ];

    protected $casts = [
        'toleransi_telat_menit'       => 'integer',
        'kuota_cuti_tahunan'          => 'integer',
        'pakai_cutoff_bulanan'        => 'boolean',
        'cutoff_tanggal_mulai'        => 'integer',
        'cutoff_tanggal_selesai'      => 'integer',
        'tarif_lembur_tetap_per_jam'  => 'float',
        'pengali_lembur_hari_kerja'   => 'float',
        'pengali_lembur_hari_libur'   => 'float',
        'uang_makan_lembur_nominal'   => 'float',
        'min_jam_uang_makan_lembur'   => 'float',
        'potongan_telat_per_kali'     => 'float',
        'potongan_alpha_per_hari'     => 'float',
        'potongan_sakit_tanpa_surat'  => 'boolean',
        'persen_bpjs_kesehatan'       => 'float',
        'persen_bpjs_ketenagakerjaan' => 'float',
        'persen_bpjs_tk_perusahaan'   => 'float',
        'persen_bpjs_kes_perusahaan'  => 'float',
        'auto_hitung_bpjs'            => 'boolean',
        'tarif_protol_default'        => 'float',
        'tarif_packing_default'       => 'float',
        'tarif_lain_borongan_default' => 'float',
        'tunj_hadir_borongan_default' => 'float',
        'tunj_makan_borongan_default' => 'float',
    ];

    public static function current(): self
    {
        return static::first() ?? static::create([
            'jam_masuk_standar'           => '08:00',
            'jam_pulang_standar'          => '17:00',
            'toleransi_telat_menit'       => 10,
            'kuota_cuti_tahunan'          => 12,
            'pakai_cutoff_bulanan'        => true,
            'cutoff_tanggal_mulai'        => 26,
            'cutoff_tanggal_selesai'      => 25,
            'metode_lembur'               => 'PER_173',
            'tarif_lembur_tetap_per_jam'  => 15000,
            'pengali_lembur_hari_kerja'   => 1.00,
            'pengali_lembur_hari_libur'   => 2.00,
            'uang_makan_lembur_nominal'   => 15000,
            'min_jam_uang_makan_lembur'   => 2.00,
            'potongan_telat_per_kali'     => 10000,
            'metode_potongan_telat'       => 'FLAT',
            'potongan_alpha_per_hari'     => 0,
            'potongan_sakit_tanpa_surat'  => false,
            'persen_bpjs_kesehatan'       => 1.00,
            'persen_bpjs_ketenagakerjaan' => 2.00,
            'auto_hitung_bpjs'            => false,
        ]);
    }
}
