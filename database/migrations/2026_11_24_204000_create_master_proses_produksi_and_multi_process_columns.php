<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Master Proses Produksi terpadu lintas stasiun (Printing, Die Making, Finishing)
        if (! Schema::hasTable('master_proses_produksi')) {
            Schema::create('master_proses_produksi', function (Blueprint $table) {
                $table->id();
                $table->string('kode_proses', 50)->unique();
                $table->enum('stasiun', ['printing', 'diemaking', 'finishing'])->index();
                $table->string('nama_proses', 120);
                $table->unsignedSmallInteger('urutan')->default(1);
                $table->string('keterangan')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Upgrade tabel printings: longgarkan enum & tambah proses_list (JSON) + status_laporan
        if (Schema::hasTable('printings')) {
            DB::statement("ALTER TABLE printings MODIFY COLUMN proses_printing VARCHAR(191) NULL");
            DB::statement("ALTER TABLE printings MODIFY COLUMN tahap_printing VARCHAR(100) NULL DEFAULT 'proses_cetak'");

            Schema::table('printings', function (Blueprint $table) {
                if (! Schema::hasColumn('printings', 'proses_list')) {
                    $table->json('proses_list')->nullable()->after('proses_printing');
                }
                if (! Schema::hasColumn('printings', 'status_laporan')) {
                    $table->string('status_laporan', 30)->default('selesai')->after('tahap_printing');
                }
            });
        }

        // 3. Upgrade tabel diemakings: longgarkan enum & tambah proses_list, status_laporan, error_production_id, catatan
        if (Schema::hasTable('diemakings')) {
            DB::statement("ALTER TABLE diemakings MODIFY COLUMN proses_diemaking VARCHAR(191) NULL");
            DB::statement("ALTER TABLE diemakings MODIFY COLUMN tahap_diemaking VARCHAR(100) NULL DEFAULT 'Laporan Akhir'");
            DB::statement("ALTER TABLE diemakings MODIFY COLUMN note_waste VARCHAR(191) NULL");
            DB::statement("ALTER TABLE diemakings MODIFY COLUMN keterangan_spk VARCHAR(100) NULL DEFAULT 'Reguler'");

            Schema::table('diemakings', function (Blueprint $table) {
                if (! Schema::hasColumn('diemakings', 'proses_list')) {
                    $table->json('proses_list')->nullable()->after('proses_diemaking');
                }
                if (! Schema::hasColumn('diemakings', 'status_laporan')) {
                    $table->string('status_laporan', 30)->default('selesai')->after('tahap_diemaking');
                }
                if (! Schema::hasColumn('diemakings', 'error_production_id')) {
                    $table->foreignId('error_production_id')
                        ->nullable()
                        ->after('semi_waste')
                        ->constrained('error_productions')
                        ->nullOnDelete();
                }
                if (! Schema::hasColumn('diemakings', 'catatan')) {
                    $table->text('catatan')->nullable()->after('keterangan_spk');
                }
            });
        }

        // 4. Upgrade tabel master_finishing: longgarkan enum & tambah proses_list + status_laporan
        if (Schema::hasTable('master_finishing')) {
            DB::statement("ALTER TABLE master_finishing MODIFY COLUMN proses_finishing VARCHAR(191) NULL");
            DB::statement("ALTER TABLE master_finishing MODIFY COLUMN tahap_finishing VARCHAR(100) NULL DEFAULT 'reguler'");
            DB::statement("ALTER TABLE master_finishing MODIFY COLUMN keterangan_spk VARCHAR(100) NULL DEFAULT 'reguler'");

            Schema::table('master_finishing', function (Blueprint $table) {
                if (! Schema::hasColumn('master_finishing', 'proses_list')) {
                    $table->json('proses_list')->nullable()->after('proses_finishing');
                }
                if (! Schema::hasColumn('master_finishing', 'status_laporan')) {
                    $table->string('status_laporan', 30)->default('selesai')->after('tahap_finishing');
                }
            });
        }

        // 5. Tabel detail waste Die Making
        if (! Schema::hasTable('diemaking_wastes')) {
            Schema::create('diemaking_wastes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('diemaking_id')
                    ->constrained('diemakings')
                    ->cascadeOnDelete();
                $table->foreignId('error_production_id')
                    ->constrained('error_productions')
                    ->cascadeOnDelete();
                $table->decimal('qty_waste', 12, 2)->default(0);
                $table->string('catatan')->nullable();
                $table->timestamps();
            });
        }

        // 6. Tabel detail waste Finishing
        if (! Schema::hasTable('finishing_wastes')) {
            Schema::create('finishing_wastes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('master_finishing_id')
                    ->constrained('master_finishing')
                    ->cascadeOnDelete();
                $table->foreignId('error_production_id')
                    ->constrained('error_productions')
                    ->cascadeOnDelete();
                $table->decimal('qty_waste', 12, 2)->default(0);
                $table->string('catatan')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('finishing_wastes');
        Schema::dropIfExists('diemaking_wastes');

        if (Schema::hasTable('master_finishing')) {
            Schema::table('master_finishing', function (Blueprint $table) {
                $cols = array_filter(['proses_list', 'status_laporan'], fn ($c) => Schema::hasColumn('master_finishing', $c));
                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('diemakings')) {
            Schema::table('diemakings', function (Blueprint $table) {
                if (Schema::hasColumn('diemakings', 'error_production_id')) {
                    $table->dropConstrainedForeignId('error_production_id');
                }
                $cols = array_filter(['proses_list', 'status_laporan', 'catatan'], fn ($c) => Schema::hasColumn('diemakings', $c));
                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('printings')) {
            Schema::table('printings', function (Blueprint $table) {
                $cols = array_filter(['proses_list', 'status_laporan'], fn ($c) => Schema::hasColumn('printings', $c));
                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        Schema::dropIfExists('master_proses_produksi');
    }
};
