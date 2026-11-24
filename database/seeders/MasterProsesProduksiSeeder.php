<?php

namespace Database\Seeders;

use App\Models\MasterProsesProduksi;
use App\Models\MesinDieMaking;
use App\Models\OperatorDiemaking;
use Illuminate\Database\Seeder;

class MasterProsesProduksiSeeder extends Seeder
{
    /**
     * Seed Master Proses Produksi untuk Printing (termasuk Flexo inline), Die Making, dan Finishing,
     * sekaligus data awal Mesin & Operator Die Making agar modul Die Making siap pakai.
     */
    public function run(): void
    {
        $catalog = [
            // === PRINTING (Offset & Flexo Inline) ===
            ['kode_proses' => 'PRT-001', 'stasiun' => 'printing', 'nama_proses' => 'Color Printing', 'urutan' => 1, 'keterangan' => 'Cetak warna utama CMYK / Pantone'],
            ['kode_proses' => 'PRT-002', 'stasiun' => 'printing', 'nama_proses' => 'Cold Foil', 'urutan' => 2, 'keterangan' => 'Aplikasi cold foil inline pada mesin flexo'],
            ['kode_proses' => 'PRT-003', 'stasiun' => 'printing', 'nama_proses' => 'Spot UV', 'urutan' => 3, 'keterangan' => 'Coating Spot UV lokal / blok'],
            ['kode_proses' => 'PRT-004', 'stasiun' => 'printing', 'nama_proses' => 'Furnish / Varnish UV', 'urutan' => 4, 'keterangan' => 'Coating OPV / UV Varnish menyeluruh'],
            ['kode_proses' => 'PRT-005', 'stasiun' => 'printing', 'nama_proses' => 'Drip Off', 'urutan' => 5, 'keterangan' => 'Efek matte & glossy drip off'],
            ['kode_proses' => 'PRT-006', 'stasiun' => 'printing', 'nama_proses' => 'Emboss Inline (Flexo)', 'urutan' => 6, 'keterangan' => 'Emboss timbul langsung di mesin flexo'],
            ['kode_proses' => 'PRT-007', 'stasiun' => 'printing', 'nama_proses' => 'Tempel Sticker / Label Inline', 'urutan' => 7, 'keterangan' => 'Aplikasi sticker / label / hologram inline'],
            ['kode_proses' => 'PRT-008', 'stasiun' => 'printing', 'nama_proses' => 'Laminasi Inline', 'urutan' => 8, 'keterangan' => 'Laminasi glossy / doff langsung di jalur cetak'],
            ['kode_proses' => 'PRT-009', 'stasiun' => 'printing', 'nama_proses' => 'Rotary Die-Cut / Potong Inline', 'urutan' => 9, 'keterangan' => 'Pemotongan / pond inline mesin web'],

            // === DIE MAKING ===
            ['kode_proses' => 'DMK-001', 'stasiun' => 'diemaking', 'nama_proses' => 'UV Varnish', 'urutan' => 1, 'keterangan' => 'Pelapisan UV Varnish offline'],
            ['kode_proses' => 'DMK-002', 'stasiun' => 'diemaking', 'nama_proses' => 'Hot Print / Foil Stamping', 'urutan' => 2, 'keterangan' => 'Poli emas / perak / hologram panas'],
            ['kode_proses' => 'DMK-003', 'stasiun' => 'diemaking', 'nama_proses' => 'UV Spot', 'urutan' => 3, 'keterangan' => 'UV Spot presisi area tertentu'],
            ['kode_proses' => 'DMK-004', 'stasiun' => 'diemaking', 'nama_proses' => 'UV Hologram', 'urutan' => 4, 'keterangan' => 'Efek hologram UV khusus'],
            ['kode_proses' => 'DMK-005', 'stasiun' => 'diemaking', 'nama_proses' => 'Emboss / Deboss', 'urutan' => 5, 'keterangan' => 'Cetak timbul / cekung dengan klise'],
            ['kode_proses' => 'DMK-006', 'stasiun' => 'diemaking', 'nama_proses' => 'Pond / Die-Cutting', 'urutan' => 6, 'keterangan' => 'Potong bentuk pola menggunakan pisau pond'],
            ['kode_proses' => 'DMK-007', 'stasiun' => 'diemaking', 'nama_proses' => 'Creasing / Rel Lipat', 'urutan' => 7, 'keterangan' => 'Pembuatan garis lipat kemasan'],
            ['kode_proses' => 'DMK-008', 'stasiun' => 'diemaking', 'nama_proses' => 'Perforasi', 'urutan' => 8, 'keterangan' => 'Lubang sobekan / perforasi'],

            // === FINISHING ===
            ['kode_proses' => 'FIN-001', 'stasiun' => 'finishing', 'nama_proses' => 'Protol (Stripping)', 'urutan' => 1, 'keterangan' => 'Pelepasan sisa potongan / waste pond'],
            ['kode_proses' => 'FIN-002', 'stasiun' => 'finishing', 'nama_proses' => 'Sorter / QC Cek Visual', 'urutan' => 2, 'keterangan' => 'Sortir kelayakan kualitas cetak & potong'],
            ['kode_proses' => 'FIN-003', 'stasiun' => 'finishing', 'nama_proses' => 'Lem / Folder Gluer', 'urutan' => 3, 'keterangan' => 'Pengeleman lipatan dus / kemasan'],
            ['kode_proses' => 'FIN-004', 'stasiun' => 'finishing', 'nama_proses' => 'Tempel Sticker / Label', 'urutan' => 4, 'keterangan' => 'Pemasangan stiker / segel tambahan'],
            ['kode_proses' => 'FIN-005', 'stasiun' => 'finishing', 'nama_proses' => 'Folding / Lipat Manual', 'urutan' => 5, 'keterangan' => 'Pelipatan kemasan secara manual'],
            ['kode_proses' => 'FIN-006', 'stasiun' => 'finishing', 'nama_proses' => 'Window Patching', 'urutan' => 6, 'keterangan' => 'Pemasangan plastik mika jendela kemasan'],
            ['kode_proses' => 'FIN-007', 'stasiun' => 'finishing', 'nama_proses' => 'Bundling / Siap Packing', 'urutan' => 7, 'keterangan' => 'Pengikatan & penyusunan barang jadi siap masuk packaging'],
        ];

        foreach ($catalog as $row) {
            MasterProsesProduksi::updateOrCreate(
                ['kode_proses' => $row['kode_proses']],
                array_merge($row, ['is_active' => true])
            );
        }

        // Seed data awal Mesin & Operator Die Making jika belum ada (idempotent)
        $mesins = [
            [
                'nama_mesin'  => 'Mesin Pond Auto Bobst SP-102',
                'jenis_mesin' => 'Auto Die-Cutting',
                'kapasitas'   => 6000,
                'proses'      => 'Pond / Die-Cutting, Creasing, Emboss',
                'status'      => 'aktif',
            ],
            [
                'nama_mesin'  => 'Mesin Hot Print & Emboss Semi-Auto',
                'jenis_mesin' => 'Foil Stamping & Emboss',
                'kapasitas'   => 3000,
                'proses'      => 'Hot Print / Foil Stamping, Emboss / Deboss',
                'status'      => 'aktif',
            ],
            [
                'nama_mesin'  => 'Mesin UV Spot & Varnish Offline',
                'jenis_mesin' => 'Screen / Roller UV Coating',
                'kapasitas'   => 4500,
                'proses'      => 'UV Varnish, UV Spot, UV Hologram',
                'status'      => 'aktif',
            ],
        ];

        foreach ($mesins as $m) {
            MesinDieMaking::firstOrCreate(
                ['nama_mesin' => $m['nama_mesin']],
                $m
            );
        }

        foreach (['Hendra (Die Making)', 'Dedi (Pond & Hot Print)'] as $opName) {
            OperatorDiemaking::firstOrCreate(['nama_operator' => $opName]);
        }
    }
}
