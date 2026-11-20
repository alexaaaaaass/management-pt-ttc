<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pracetak_processes', function (Blueprint $table) {
            // A. Trial Design
            if (!Schema::hasColumn('pracetak_processes', 'status_file')) {
                $table->string('status_file')->nullable()->default('ada');
            }
            if (!Schema::hasColumn('pracetak_processes', 'status_kesiapan_file')) {
                $table->string('status_kesiapan_file')->nullable()->default('siap_cetak');
            }
            if (!Schema::hasColumn('pracetak_processes', 'toleransi_hasil')) {
                $table->string('toleransi_hasil')->nullable();
            }
            if (!Schema::hasColumn('pracetak_processes', 'catatan_design')) {
                $table->text('catatan_design')->nullable();
            }

            // B. Prove Print
            if (!Schema::hasColumn('pracetak_processes', 'is_prove_print')) {
                $table->boolean('is_prove_print')->default(false);
            }
            if (!Schema::hasColumn('pracetak_processes', 'bahan_prove_print')) {
                $table->string('bahan_prove_print')->nullable();
            }
            if (!Schema::hasColumn('pracetak_processes', 'status_prove_print')) {
                $table->string('status_prove_print')->nullable()->default('menunggu_acc');
            }
            if (!Schema::hasColumn('pracetak_processes', 'arsip_proses_prove')) {
                $table->string('arsip_proses_prove')->nullable();
            }

            // C. Plate Making
            if (!Schema::hasColumn('pracetak_processes', 'jumlah_warna_plate')) {
                $table->integer('jumlah_warna_plate')->nullable()->default(4);
            }
            if (!Schema::hasColumn('pracetak_processes', 'detail_warna_plate')) {
                $table->string('detail_warna_plate')->nullable();
            }
            if (!Schema::hasColumn('pracetak_processes', 'kerusakan_plate')) {
                $table->integer('kerusakan_plate')->default(0);
            }
            if (!Schema::hasColumn('pracetak_processes', 'catatan_kerusakan_plate')) {
                $table->text('catatan_kerusakan_plate')->nullable();
            }

            // D. Penyimpanan Material & Finishing
            if (!Schema::hasColumn('pracetak_processes', 'lokasi_simpan_plate')) {
                $table->string('lokasi_simpan_plate')->nullable();
            }
            if (!Schema::hasColumn('pracetak_processes', 'material_pendukung')) {
                $table->string('material_pendukung')->nullable();
            }
            if (!Schema::hasColumn('pracetak_processes', 'toleransi_rusak_hilang')) {
                $table->string('toleransi_rusak_hilang')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pracetak_processes', function (Blueprint $table) {
            $cols = [
                'status_file',
                'status_kesiapan_file',
                'toleransi_hasil',
                'catatan_design',
                'is_prove_print',
                'bahan_prove_print',
                'status_prove_print',
                'arsip_proses_prove',
                'jumlah_warna_plate',
                'detail_warna_plate',
                'kerusakan_plate',
                'catatan_kerusakan_plate',
                'lokasi_simpan_plate',
                'material_pendukung',
                'toleransi_rusak_hilang',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('pracetak_processes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
