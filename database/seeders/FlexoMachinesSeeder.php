<?php

namespace Database\Seeders;

use App\Models\MasterPrinting;
use App\Models\MesinDieMaking;
use App\Models\MesinFinis;
use App\Models\MesinPraCetak;
use App\Models\Printing;
use Illuminate\Database\Seeder;

class FlexoMachinesSeeder extends Seeder
{
    /**
     * Daftarkan 3 Mesin Utama Flexo:
     * 1. Printing Flexo
     * 2. Diecut Flexo
     * 3. CTP / Plat Flexo
     */
    public function run(): void
    {
        // 1. Migrasikan / Rename mesin di MasterPrinting jika masih pakai nama offset lama
        $sm74 = MasterPrinting::where('nama_mesin', 'SM 74')->first();
        if ($sm74) {
            $sm74->update([
                'nama_mesin'  => 'Printing Flexo',
                'jenis_mesin' => 'FLEXOGRAPHY',
                'kapasitas'   => 150,
                'proses'      => 'CETAK ROLL FLEXO (FULL COLOR)',
                'status'      => 1,
            ]);
        } else {
            MasterPrinting::firstOrCreate(
                ['nama_mesin' => 'Printing Flexo'],
                [
                    'jenis_mesin' => 'FLEXOGRAPHY',
                    'kapasitas'   => 150,
                    'proses'      => 'CETAK ROLL FLEXO (FULL COLOR)',
                    'status'      => 1,
                ]
            );
        }

        $flexoOld = MasterPrinting::where('nama_mesin', 'FLEXO')->first();
        if ($flexoOld) {
            $flexoOld->update([
                'nama_mesin'  => 'Diecut Flexo',
                'jenis_mesin' => 'ROTARY / FLATBED DIECUT',
                'kapasitas'   => 150,
                'proses'      => 'POND & DIECUTTING LABEL / BOX',
                'status'      => 1,
            ]);
        } else {
            MasterPrinting::firstOrCreate(
                ['nama_mesin' => 'Diecut Flexo'],
                [
                    'jenis_mesin' => 'ROTARY / FLATBED DIECUT',
                    'kapasitas'   => 150,
                    'proses'      => 'POND & DIECUTTING LABEL / BOX',
                    'status'      => 1,
                ]
            );
        }

        $gto52 = MasterPrinting::where('nama_mesin', 'GTO 52')->first();
        if ($gto52) {
            $gto52->update([
                'nama_mesin'  => 'CTP / Plat Flexo',
                'jenis_mesin' => 'COMPUTER TO PLATE (CTP)',
                'kapasitas'   => 100,
                'proses'      => 'PEMBUATAN PLAT FOTOPOLIMER FLEXO',
                'status'      => 1,
            ]);
        } else {
            MasterPrinting::firstOrCreate(
                ['nama_mesin' => 'CTP / Plat Flexo'],
                [
                    'jenis_mesin' => 'COMPUTER TO PLATE (CTP)',
                    'kapasitas'   => 100,
                    'proses'      => 'PEMBUATAN PLAT FOTOPOLIMER FLEXO',
                    'status'      => 1,
                ]
            );
        }

        // Hapus mesin lama selain 3 mesin utama Flexo ini jika ada
        MasterPrinting::whereNotIn('nama_mesin', [
            'Printing Flexo',
            'Diecut Flexo',
            'CTP / Plat Flexo',
        ])->delete();

        // 2. Daftarkan di MesinPraCetak
        try {
            MesinPraCetak::firstOrCreate(
                ['nama_mesin' => 'CTP / Plat Flexo'],
                [
                    'jenis_mesin' => 'CTP FLEXO',
                    'kapasitas'   => 100,
                ]
            );
        } catch (\Throwable $e) {}

        // 3. Daftarkan di MesinFinis & MesinDieMaking
        try {
            MesinFinis::firstOrCreate(
                ['nama_mesin' => 'Diecut Flexo'],
                [
                    'jenis_mesin' => 'ROTARY DIECUT',
                    'kapasitas'   => 150,
                ]
            );
        } catch (\Throwable $e) {}

        try {
            MesinDieMaking::firstOrCreate(
                ['nama_mesin' => 'Diecut Flexo'],
                [
                    'jenis_mesin' => 'PISAU POND / DIECUT',
                    'kapasitas'   => 150,
                ]
            );
        } catch (\Throwable $e) {}
    }
}
