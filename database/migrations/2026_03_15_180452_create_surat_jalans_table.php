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
        Schema::create('surat_jalans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_surat_jalan', 100)->nullable();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('cascade');
            $table->foreignId('spk_id')->nullable()->constrained('master_spk')->onDelete('set null');
            $table->bigInteger('qty_pengiriman')->default(0);
            $table->date('tanggal_surat_jalan');
            $table->text('alamat_tujuan');
            $table->string('transportasi', 255);
            $table->string('no_polisi', 100);
            $table->string('driver', 255);
            $table->string('pengirim', 255);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_jalans');
    }
};
