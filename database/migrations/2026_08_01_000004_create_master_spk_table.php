<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_spk', function (Blueprint $table) {
            $table->id();
            $table->string('no_spk', 255)->unique()->nullable();
            $table->string('kode_ik', 50)->nullable();
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->onDelete('cascade');
            $table->string('production_plan', 255)->nullable();
            $table->enum('status', ['ON PROCESS', 'FINISH'])->default('ON PROCESS');
            $table->date('tanggal_estimasi_selesai')->nullable();
            $table->date('tanggal_po')->nullable();
            $table->integer('up_satu')->nullable();
            $table->integer('up_dua')->nullable();
            $table->integer('up_tiga')->nullable();
            $table->string('ukuran_potong', 100)->nullable();
            $table->string('ukuran_cetak', 100)->nullable();
            $table->text('spesifikasi_kertas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_spk');
    }
};
