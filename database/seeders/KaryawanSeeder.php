<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Karyawan;

class KaryawanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Budi Santoso',
                'nip' => 'KRY001',
                'tempat_lahir' => 'Surabaya',
                'tanggal_lahir' => '1990-05-10',
                'departemen_id' => 1,
                'jabatan' => 'Manager',
                'kantor' => 'Pusat',
                'tanggal_masuk_kerja' => '2020-01-01',
                'no_telp' => '081234567890',
                'nik' => '3578123456780001',
                'no_ktp' => '3578123456780001',
                'jenis_kelamin' => 'Laki-laki',
                'agama' => 'Islam',

                'gaji_pokok' => 8000000,
                'tipe_gaji' => 'bulanan',
                'tunjangan_kompetensi' => 1000000,
                'tunjangan_jabatan' => 1500000,
                'tunjangan_intensif' => 500000,

                'nama_npwp' => 'Budi Santoso',
                'nomor_npwp' => '12.345.678.9-012.000',
                'alamat_npwp' => 'Surabaya',
                'tanggal_npwp' => '2020-02-01',
                'PTKP' => 'K/0',

                'nama_bank' => 'BCA',
                'rekening_atas_nama' => 'Budi Santoso',
                'no_rekening' => '1234567890',

                'nama_bpjs' => 'Budi Santoso',
                'bpjs_kesehatan' => 'BPJS001',
                'bpjs_ketenagakerjaan' => 'BPJSTK001'
            ],
            [
                'nama' => 'Siti Aminah',
                'nip' => 'KRY002',
                'tempat_lahir' => 'Malang',
                'tanggal_lahir' => '1995-08-15',
                'departemen_id' => 2,
                'jabatan' => 'Staff HR',
                'kantor' => 'Cabang',
                'tanggal_masuk_kerja' => '2021-03-01',
                'no_telp' => '081298765432',
                'nik' => '3578123456780002',
                'no_ktp' => '3578123456780002',
                'jenis_kelamin' => 'Perempuan',
                'agama' => 'Islam',

                'gaji_pokok' => 5000000,
                'tipe_gaji' => 'bulanan',
                'tunjangan_kompetensi' => 500000,
                'tunjangan_jabatan' => 300000,
                'tunjangan_intensif' => 200000,

                'nama_npwp' => 'Siti Aminah',
                'nomor_npwp' => '98.765.432.1-098.000',
                'alamat_npwp' => 'Malang',
                'tanggal_npwp' => '2021-04-01',
                'PTKP' => 'TK/0',

                'nama_bank' => 'BRI',
                'rekening_atas_nama' => 'Siti Aminah',
                'no_rekening' => '9876543210',

                'nama_bpjs' => 'Siti Aminah',
                'bpjs_kesehatan' => 'BPJS002',
                'bpjs_ketenagakerjaan' => 'BPJSTK002'
            ],
        ];

        foreach ($data as $item) {
            Karyawan::create($item);
        }
    }
}