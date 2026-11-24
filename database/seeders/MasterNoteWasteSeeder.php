<?php

namespace Database\Seeders;

use App\Models\ErrorProduction;
use Illuminate\Database\Seeder;

class MasterNoteWasteSeeder extends Seeder
{
    /**
     * Seed daftar standar Master Note Waste (penyebab kerusakan/waste produksi printing & finishing).
     */
    public function run(): void
    {
        $items = [
            ['kode_error' => 'WST-001', 'nama_error' => 'CETAK MIRING'],
            ['kode_error' => 'WST-002', 'nama_error' => 'REGISTER GESER / TIDAK PRESISI'],
            ['kode_error' => 'WST-003', 'nama_error' => 'WARNA BELANG / TIDAK RATA'],
            ['kode_error' => 'WST-004', 'nama_error' => 'TINTA BLEBER / KOTOR / BANJIR'],
            ['kode_error' => 'WST-005', 'nama_error' => 'CETAK LUNTUR / KURANG KERING'],
            ['kode_error' => 'WST-006', 'nama_error' => 'SETTING AWAL MESIN (MAKE READY)'],
            ['kode_error' => 'WST-007', 'nama_error' => 'SAMBUNGAN BAHAN / SPLICE'],
            ['kode_error' => 'WST-008', 'nama_error' => 'BAHAN KUSUT / ROBEK / LIPAT'],
            ['kode_error' => 'WST-009', 'nama_error' => 'PLATE KOTOR / RUSAK / TERGORES'],
            ['kode_error' => 'WST-010', 'nama_error' => 'FOIL / SPOT UV TIDAK MENEMPEL'],
            ['kode_error' => 'WST-011', 'nama_error' => 'POTONGAN TIDAK PRESISI'],
            ['kode_error' => 'WST-012', 'nama_error' => 'LAIN-LAIN'],
        ];

        foreach ($items as $item) {
            ErrorProduction::updateOrCreate(
                ['kode_error' => $item['kode_error']],
                ['nama_error' => $item['nama_error']]
            );
        }
    }
}
