<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Perbaiki & perluas pracetak_processes (fix enum prove_print + pre-order + costing + handover)
        if (Schema::hasTable('pracetak_processes')) {
            // Ubah status dari enum terbatas menjadi VARCHAR(30) agar mendukung prove_print & handed_over
            try {
                DB::statement("ALTER TABLE `pracetak_processes` MODIFY `status` VARCHAR(30) NOT NULL DEFAULT 'design'");
            } catch (\Throwable $e) {
                // Fallback jika bukan MySQL (misal SQLite test)
            }

            // Jadikan spk_id nullable agar mendukung Pre-Order Trial Design & Prove Print dari Approval Form
            try {
                DB::statement("ALTER TABLE `pracetak_processes` MODIFY `spk_id` BIGINT UNSIGNED NULL");
            } catch (\Throwable $e) {
                // Fallback
            }

            Schema::table('pracetak_processes', function (Blueprint $table) {
                if (! Schema::hasColumn('pracetak_processes', 'approval_form_id')) {
                    $table->foreignId('approval_form_id')
                        ->nullable()
                        ->after('spk_id')
                        ->constrained('approval_forms')
                        ->nullOnDelete();
                }
                if (! Schema::hasColumn('pracetak_processes', 'order_confirmation_id')) {
                    $table->foreignId('order_confirmation_id')
                        ->nullable()
                        ->after('approval_form_id')
                        ->constrained('order_confirmations')
                        ->nullOnDelete();
                }
                if (! Schema::hasColumn('pracetak_processes', 'finish_good_item_id')) {
                    $table->foreignId('finish_good_item_id')
                        ->nullable()
                        ->after('order_confirmation_id')
                        ->constrained('finish_good_items')
                        ->nullOnDelete();
                }
                if (! Schema::hasColumn('pracetak_processes', 'is_repeat_order')) {
                    $table->boolean('is_repeat_order')->default(false)->after('ket_spk');
                }
                if (! Schema::hasColumn('pracetak_processes', 'plate_archive_id')) {
                    $table->unsignedBigInteger('plate_archive_id')->nullable()->after('is_repeat_order');
                }
                if (! Schema::hasColumn('pracetak_processes', 'estimasi_biaya_plate')) {
                    $table->decimal('estimasi_biaya_plate', 18, 2)->default(0);
                }
                if (! Schema::hasColumn('pracetak_processes', 'actual_design_cost')) {
                    $table->decimal('actual_design_cost', 18, 2)->default(0);
                }
                if (! Schema::hasColumn('pracetak_processes', 'actual_prove_cost')) {
                    $table->decimal('actual_prove_cost', 18, 2)->default(0);
                }
                if (! Schema::hasColumn('pracetak_processes', 'actual_material_cost')) {
                    $table->decimal('actual_material_cost', 18, 2)->default(0);
                }
                if (! Schema::hasColumn('pracetak_processes', 'actual_process_cost')) {
                    $table->decimal('actual_process_cost', 18, 2)->default(0);
                }
                if (! Schema::hasColumn('pracetak_processes', 'actual_total_cost')) {
                    $table->decimal('actual_total_cost', 18, 2)->default(0);
                }
                if (! Schema::hasColumn('pracetak_processes', 'cost_variance')) {
                    $table->decimal('cost_variance', 18, 2)->default(0);
                }
                if (! Schema::hasColumn('pracetak_processes', 'material_posted_at')) {
                    $table->timestamp('material_posted_at')->nullable();
                }
                if (! Schema::hasColumn('pracetak_processes', 'handed_over_at')) {
                    $table->timestamp('handed_over_at')->nullable();
                }
                if (! Schema::hasColumn('pracetak_processes', 'handed_over_to')) {
                    $table->string('handed_over_to', 150)->nullable();
                }
            });
        }

        // 2. Tambah tarif standar mesin CTP di mesin_pra_cetaks
        if (Schema::hasTable('mesin_pra_cetaks')) {
            Schema::table('mesin_pra_cetaks', function (Blueprint $table) {
                if (! Schema::hasColumn('mesin_pra_cetaks', 'tarif_per_plate')) {
                    $table->decimal('tarif_per_plate', 18, 2)->default(0);
                }
                if (! Schema::hasColumn('mesin_pra_cetaks', 'tarif_per_jam')) {
                    $table->decimal('tarif_per_jam', 18, 2)->default(0);
                }
            });
        }

        // 3. Tingkatkan presisi pecahan sheet (decimal 12,4) pada stok & histori plate prepress
        if (Schema::hasTable('pracetak_material_stocks')) {
            try {
                DB::statement("ALTER TABLE `pracetak_material_stocks` MODIFY `stok_sheet_utuh` DECIMAL(12,4) NOT NULL DEFAULT 0, MODIFY `total_masuk` DECIMAL(12,4) NOT NULL DEFAULT 0, MODIFY `total_keluar` DECIMAL(12,4) NOT NULL DEFAULT 0");
            } catch (\Throwable $e) {
            }
        }
        if (Schema::hasTable('pracetak_material_movements')) {
            try {
                DB::statement("ALTER TABLE `pracetak_material_movements` MODIFY `qty_masuk` DECIMAL(12,4) NOT NULL DEFAULT 0, MODIFY `qty_keluar` DECIMAL(12,4) NOT NULL DEFAULT 0, MODIFY `saldo_sebelum` DECIMAL(12,4) NOT NULL DEFAULT 0, MODIFY `saldo_setelah` DECIMAL(12,4) NOT NULL DEFAULT 0");
            } catch (\Throwable $e) {
            }
        }

        // 4. Tabel detail pemakaian material plate per proses Pra Cetak (Tahap C & Remake)
        if (! Schema::hasTable('pracetak_material_usages')) {
            Schema::create('pracetak_material_usages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pracetak_process_id')
                    ->constrained('pracetak_processes')
                    ->cascadeOnDelete();
                $table->foreignId('master_material_plate_id')
                    ->constrained('master_material_plates')
                    ->restrictOnDelete();
                $table->enum('jenis_pemakaian', ['produksi', 'remake_rusak', 'prove'])
                    ->default('produksi')
                    ->index();
                $table->integer('jumlah_plate')->default(1);
                $table->decimal('panjang_potong_mm', 10, 2)->default(0);
                $table->decimal('lebar_potong_mm', 10, 2)->default(0);
                $table->decimal('luas_terpakai_cm2', 14, 2)->default(0);
                $table->decimal('qty_sheet_terpakai', 12, 4)->default(0);
                $table->decimal('harga_satuan_snapshot', 18, 2)->default(0);
                $table->decimal('total_biaya', 18, 2)->default(0);
                $table->enum('status', ['draft', 'posted', 'reversed'])->default('draft')->index();
                $table->text('alasan_kerusakan')->nullable();
                $table->foreignId('posted_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->timestamp('posted_at')->nullable();
                $table->timestamps();
            });
        }

        // 5. Tabel Arsip Plate Jadi & Penyimpanan Rak (Tahap D & Repeat Order)
        if (! Schema::hasTable('pracetak_plate_archives')) {
            Schema::create('pracetak_plate_archives', function (Blueprint $table) {
                $table->id();
                $table->string('kode_arsip', 50)->unique();
                $table->foreignId('pracetak_process_id')
                    ->nullable()
                    ->constrained('pracetak_processes')
                    ->nullOnDelete();
                $table->foreignId('finish_good_item_id')
                    ->nullable()
                    ->constrained('finish_good_items')
                    ->nullOnDelete();
                $table->foreignId('customer_id')
                    ->nullable()
                    ->constrained('customers')
                    ->nullOnDelete();
                $table->string('nama_plat', 255);
                $table->integer('jumlah_warna')->default(4);
                $table->string('detail_warna', 255)->nullable();
                $table->string('dimensi_plate_mm', 100)->nullable();
                $table->string('lokasi_rak', 150)->nullable()->index();
                $table->text('material_pendukung')->nullable();
                $table->string('toleransi_rusak_hilang', 255)->nullable();
                $table->integer('max_pemakaian')->nullable();
                $table->integer('jumlah_dipakai')->default(0);
                $table->enum('status_kondisi', ['tersimpan', 'dipakai_printing', 'perlu_remake', 'rusak_hilang', 'retired'])
                    ->default('tersimpan')
                    ->index();
                $table->timestamp('last_used_at')->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }

        // 6. Tambah dukungan Biaya Plate Terpisah (Cliché Charge) pada Order Confirmation
        if (Schema::hasTable('order_confirmations')) {
            Schema::table('order_confirmations', function (Blueprint $table) {
                if (! Schema::hasColumn('order_confirmations', 'biaya_plate_total')) {
                    $table->decimal('biaya_plate_total', 18, 2)->default(0)->after('subtotal');
                }
            });
        }

        if (Schema::hasTable('order_confirmation_items')) {
            Schema::table('order_confirmation_items', function (Blueprint $table) {
                if (! Schema::hasColumn('order_confirmation_items', 'plate_charge_mode')) {
                    $table->string('plate_charge_mode', 30)->default('include_in_hpp')->after('biaya_proses');
                }
                if (! Schema::hasColumn('order_confirmation_items', 'biaya_plate_terpisah')) {
                    $table->decimal('biaya_plate_terpisah', 18, 2)->default(0)->after('plate_charge_mode');
                }
                if (! Schema::hasColumn('order_confirmation_items', 'plate_archive_id')) {
                    $table->unsignedBigInteger('plate_archive_id')->nullable()->after('biaya_plate_terpisah');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_confirmation_items')) {
            Schema::table('order_confirmation_items', function (Blueprint $table) {
                foreach (['plate_charge_mode', 'biaya_plate_terpisah', 'plate_archive_id'] as $col) {
                    if (Schema::hasColumn('order_confirmation_items', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('order_confirmations') && Schema::hasColumn('order_confirmations', 'biaya_plate_total')) {
            Schema::table('order_confirmations', function (Blueprint $table) {
                $table->dropColumn('biaya_plate_total');
            });
        }

        Schema::dropIfExists('pracetak_plate_archives');
        Schema::dropIfExists('pracetak_material_usages');

        if (Schema::hasTable('mesin_pra_cetaks')) {
            Schema::table('mesin_pra_cetaks', function (Blueprint $table) {
                foreach (['tarif_per_plate', 'tarif_per_jam'] as $col) {
                    if (Schema::hasColumn('mesin_pra_cetaks', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('pracetak_processes')) {
            Schema::table('pracetak_processes', function (Blueprint $table) {
                foreach (['approval_form_id', 'order_confirmation_id', 'finish_good_item_id'] as $fkCol) {
                    if (Schema::hasColumn('pracetak_processes', $fkCol)) {
                        try {
                            $table->dropConstrainedForeignId($fkCol);
                        } catch (\Throwable $e) {
                            $table->dropColumn($fkCol);
                        }
                    }
                }
                $cols = [
                    'is_repeat_order',
                    'plate_archive_id',
                    'estimasi_biaya_plate',
                    'actual_design_cost',
                    'actual_prove_cost',
                    'actual_material_cost',
                    'actual_process_cost',
                    'actual_total_cost',
                    'cost_variance',
                    'material_posted_at',
                    'handed_over_at',
                    'handed_over_to',
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('pracetak_processes', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
