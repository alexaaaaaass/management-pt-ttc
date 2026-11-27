<?php

namespace Database\Seeders;

use App\Models\MasterCOA;
use App\Models\NpbRequest;
use App\Models\User;
use App\Services\Finance\NpbService;
use Illuminate\Database\Seeder;

class NpbSampleSeeder extends Seeder
{
    /**
     * Seed contoh resmi NPB/KEU/XI/2026 (idempotent — tidak menimpa jika sudah ada).
     */
    public function run(): void
    {
        if (NpbRequest::where('nomor_pengajuan', 'NPB/KEU/XI/2026')->exists()) {
            return;
        }

        $coaByCode = MasterCOA::whereIn('kode_akun', [
            '1111', '1112', '1114', '1116', '1211', '1221', '1231', '1261',
            '6203', '6205', '6207', '6301', '6402', '6502', '6610',
        ])->get()->keyBy(fn ($c) => trim((string) $c->kode_akun));

        $cid = fn (string $code) => $coaByCode->get($code)?->id;
        $userId = User::orderBy('id')->value('id');

        $npb = NpbRequest::create([
            'nomor_pengajuan'   => 'NPB/KEU/XI/2026',
            'tanggal_pengajuan' => '2026-11-11',
            'departemen_kode'   => 'KEU',
            'perihal'           => 'Permohonan Pendanaan PT. Teknografi Tri Cawanaska Periode November-Desember 2026',
            'periode_utama'     => 'November 2026',
            'periode_proyeksi'  => 'Desember 2026',
            'catatan'           => "1. Nilai November merupakan kebutuhan periode berjalan; nilai Desember merupakan proyeksi dan dapat disesuaikan dengan realisasi.\n2. Pos insidental dibayarkan sesuai jadwal/tagihan dan tidak otomatis berulang setiap bulan.\n3. Lampirkan rincian tagihan, dasar perhitungan, dan bukti pendukung pada saat pengajuan/pencairan.",
            'status'            => 'APPROVED',
            'disusun_oleh'      => 'Bagian Keuangan',
            'mengetahui_nama'   => 'Kepala',
            'mengetahui_at'     => null,
            'diajukan_kepada'   => 'Direktur',
            'disetujui_oleh'    => 'Owner',
            'disetujui_at'      => '2026-11-11 14:00:00',
            'created_by'        => $userId,
        ]);

        $fundRows = [
            ['urutan' => 1, 'coa_id' => $cid('1114'), 'nama_rekening' => 'BRI Giro',        'saldo' => 1856686],
            ['urutan' => 2, 'coa_id' => $cid('1111'), 'nama_rekening' => 'BRI Tabungan',    'saldo' => 15798087],
            ['urutan' => 3, 'coa_id' => $cid('1112'), 'nama_rekening' => 'BCA Giro',        'saldo' => 4719387],
            ['urutan' => 4, 'coa_id' => null,         'nama_rekening' => 'BCA Priska',      'saldo' => 2949988],
            ['urutan' => 5, 'coa_id' => $cid('1116'), 'nama_rekening' => 'Petty Cash Juan', 'saldo' => 0],
            ['urutan' => 6, 'coa_id' => $cid('1116'), 'nama_rekening' => 'Petty Cash Yaul', 'saldo' => 0],
        ];
        foreach ($fundRows as $fp) {
            $npb->fundPositions()->create($fp);
        }

        $items = [
            // A. KEWAJIBAN TETAP PERUSAHAAN
            ['urutan' => 1,  'kategori' => 'A_KEWAJIBAN_TETAP',        'uraian' => 'Tagihan Gudang B8-B9',           'karakter' => 'Bulanan',    'coa_id' => $cid('1211'), 'nominal_utama' => 188437500, 'nominal_proyeksi' => 188437500],
            ['urutan' => 2,  'kategori' => 'A_KEWAJIBAN_TETAP',        'uraian' => 'Gaji Karyawan',                  'karakter' => 'Bulanan',    'coa_id' => $cid('6301'), 'nominal_utama' => 19852000,  'nominal_proyeksi' => 19852000],
            ['urutan' => 3,  'kategori' => 'A_KEWAJIBAN_TETAP',        'uraian' => 'BPJS Ketenagakerjaan',           'karakter' => 'Bulanan',    'coa_id' => $cid('6502'), 'nominal_utama' => 1113652,   'nominal_proyeksi' => 1113652],
            ['urutan' => 4,  'kategori' => 'A_KEWAJIBAN_TETAP',        'uraian' => 'Cicilan Kendaraan',              'karakter' => 'Bulanan',    'coa_id' => $cid('1231'), 'nominal_utama' => 53074000,  'nominal_proyeksi' => 53074000],
            // B. UTILITAS DAN OPERASIONAL
            ['urutan' => 5,  'kategori' => 'B_UTILITAS_OPERASIONAL',   'uraian' => 'PDAM',                           'karakter' => 'Bulanan',    'coa_id' => $cid('6203'), 'nominal_utama' => 130000,    'nominal_proyeksi' => 130000],
            ['urutan' => 6,  'kategori' => 'B_UTILITAS_OPERASIONAL',   'uraian' => 'Operasional Gudang',             'karakter' => 'Bulanan',    'coa_id' => $cid('6610'), 'nominal_utama' => 500000,    'nominal_proyeksi' => 500000],
            ['urutan' => 7,  'kategori' => 'B_UTILITAS_OPERASIONAL',   'uraian' => 'Listrik Trafo',                  'karakter' => 'Bulanan',    'coa_id' => $cid('6203'), 'nominal_utama' => 23684145,  'nominal_proyeksi' => 23684145],
            ['urutan' => 8,  'kategori' => 'B_UTILITAS_OPERASIONAL',   'uraian' => 'Wifi',                           'karakter' => 'Bulanan',    'coa_id' => $cid('6207'), 'nominal_utama' => 1254300,   'nominal_proyeksi' => 1254300],
            // C. KEBUTUHAN PRODUKSI DAN FASILITAS
            ['urutan' => 9,  'kategori' => 'C_PRODUKSI_FASILITAS',     'uraian' => 'Proyek Partisi Ruang Produksi',  'karakter' => 'Insidental', 'coa_id' => $cid('1261'), 'nominal_utama' => 125000000, 'nominal_proyeksi' => 0],
            ['urutan' => 10, 'kategori' => 'C_PRODUKSI_FASILITAS',     'uraian' => 'Prasarana Penunjang Mesin',      'karakter' => 'Insidental', 'coa_id' => $cid('1221'), 'nominal_utama' => 150000000, 'nominal_proyeksi' => 50000000],
            // D. PENGADAAN DAN PENGURUSAN
            ['urutan' => 11, 'kategori' => 'D_PENGADAAN_PENGURUSAN',   'uraian' => 'Clearance',                      'karakter' => 'Insidental', 'coa_id' => $cid('6402'), 'nominal_utama' => 46500000,  'nominal_proyeksi' => 0],
            // E. ADMINISTRASI DAN LEGALITAS
            ['urutan' => 12, 'kategori' => 'E_ADMINISTRASI_LEGALITAS', 'uraian' => 'Perubahan Akta dan Legalisir',   'karakter' => 'Insidental', 'coa_id' => $cid('6205'), 'nominal_utama' => 0,         'nominal_proyeksi' => 6000000],
        ];
        foreach ($items as $it) {
            $npb->items()->create($it);
        }

        app(NpbService::class)->recalculateRequest($npb);
    }
}
