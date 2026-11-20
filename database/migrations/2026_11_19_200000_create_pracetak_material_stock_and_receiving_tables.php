<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stock, Penerimaan, dan Histori Material Plate Flexo (Pra Cetak).
     * Terisolasi penuh dari Warehouse Printing / master_items / StockService existing.
     */
    public function up(): void
    {
        // 1. Header Penerimaan Material Plate
        if (!Schema::hasTable('pracetak_penerimaans')) {
            Schema::create('pracetak_penerimaans', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_penerimaan', 50)->unique();
                $table->date('tanggal_terima')->index();
                $table->foreignId('supplier_id')
                    ->nullable()
                    ->constrained('master_suppliers')
                    ->nullOnDelete();
                $table->string('no_invoice_supplier', 100)->nullable();
                $table->string('no_surat_jalan', 100)->nullable();
                $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft')->index();
                $table->decimal('total_qty', 12, 2)->default(0);
                $table->decimal('total_nominal', 18, 2)->default(0);
                $table->text('catatan')->nullable();
                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->foreignId('posted_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->timestamp('posted_at')->nullable();
                $table->timestamps();
            });
        }

        // 2. Detail Item Penerimaan Material Plate
        if (!Schema::hasTable('pracetak_penerimaan_items')) {
            Schema::create('pracetak_penerimaan_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pracetak_penerimaan_id')
                    ->constrained('pracetak_penerimaans')
                    ->cascadeOnDelete();
                $table->foreignId('master_material_plate_id')
                    ->constrained('master_material_plates')
                    ->restrictOnDelete();

                // Snapshot spesifikasi saat transaksi penerimaan
                $table->string('brand', 100);
                $table->string('tipe_plate', 100)->nullable();
                $table->decimal('ketebalan_mm', 6, 3)->default(0);
                $table->decimal('panjang_sheet_mm', 10, 2)->default(0);
                $table->decimal('lebar_sheet_mm', 10, 2)->default(0);

                $table->decimal('qty_sheet', 12, 2)->default(1);
                $table->decimal('harga_satuan', 18, 2)->default(0);
                $table->decimal('subtotal', 18, 2)->default(0);
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }

        // 3. Saldo Stok Material Plate Flexo
        if (!Schema::hasTable('pracetak_material_stocks')) {
            Schema::create('pracetak_material_stocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('master_material_plate_id')
                    ->unique()
                    ->constrained('master_material_plates')
                    ->restrictOnDelete();
                $table->decimal('stok_sheet_utuh', 12, 2)->default(0);
                $table->decimal('total_masuk', 12, 2)->default(0);
                $table->decimal('total_keluar', 12, 2)->default(0);
                $table->decimal('total_nilai_stok', 18, 2)->default(0);
                $table->decimal('harga_terakhir', 18, 2)->default(0);
                $table->timestamp('last_movement_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. Ledger Mutasi / Histori Material Plate Flexo
        if (!Schema::hasTable('pracetak_material_movements')) {
            Schema::create('pracetak_material_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('master_material_plate_id')
                    ->constrained('master_material_plates')
                    ->restrictOnDelete();
                $table->date('tanggal')->index();
                $table->string('nomor_transaksi', 100)->index();
                $table->enum('jenis_transaksi', ['IN', 'OUT', 'ADJUSTMENT'])->default('IN')->index();
                $table->string('referensi_type', 100)->nullable();
                $table->unsignedBigInteger('referensi_id')->nullable();
                $table->decimal('qty_masuk', 12, 2)->default(0);
                $table->decimal('qty_keluar', 12, 2)->default(0);
                $table->decimal('saldo_sebelum', 12, 2)->default(0);
                $table->decimal('saldo_setelah', 12, 2)->default(0);
                $table->decimal('harga_satuan', 18, 2)->default(0);
                $table->decimal('total_nominal', 18, 2)->default(0);
                $table->foreignId('supplier_id')
                    ->nullable()
                    ->constrained('master_suppliers')
                    ->nullOnDelete();
                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->index(['referensi_type', 'referensi_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pracetak_material_movements');
        Schema::dropIfExists('pracetak_material_stocks');
        Schema::dropIfExists('pracetak_penerimaan_items');
        Schema::dropIfExists('pracetak_penerimaans');
    }
};
