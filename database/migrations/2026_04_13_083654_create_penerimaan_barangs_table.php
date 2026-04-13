<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerimaan_barangs', function (Blueprint $table) {
            $table->id();

            $table->string('no_penerimaan')->unique();

            $table->foreignId('purchase_order_id')
                ->constrained('purchase_orders')
                ->cascadeOnDelete();

            $table->date('tanggal_terima');

            $table->string('no_surat_jalan')->nullable();
            $table->string('nama_pengirim')->nullable();
            $table->string('nopol_kendaraan')->nullable();

            $table->text('catatan_pengiriman')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerimaan_barangs');
    }
};