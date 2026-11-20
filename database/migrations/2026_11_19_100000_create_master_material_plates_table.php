<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master Material Plate Flexo (Pra Cetak).
     * Sengaja dipisah dari master_items agar material plate tidak tercampur
     * dengan material Printing pada alur Warehouse / StockService existing.
     */
    public function up(): void
    {
        if (Schema::hasTable('master_material_plates')) {
            return;
        }

        Schema::create('master_material_plates', function (Blueprint $table) {
            $table->id();
            $table->string('kode_material', 50)->unique();
            $table->string('nama_material', 255);
            $table->string('brand', 100)->index();
            $table->string('tipe_plate', 100)->nullable();
            $table->decimal('ketebalan_mm', 6, 3)->default(0);
            $table->decimal('panjang_sheet_mm', 10, 2)->default(0);
            $table->decimal('lebar_sheet_mm', 10, 2)->default(0);

            $table->foreignId('satuan_id')
                ->nullable()
                ->constrained('master_satuans')
                ->nullOnDelete();

            $table->decimal('harga_beli', 18, 2)->default(0);

            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained('master_suppliers')
                ->nullOnDelete();

            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->index();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_material_plates');
    }
};
