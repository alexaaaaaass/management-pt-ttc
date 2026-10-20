<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berita_acaras', function (Blueprint $table) {
            $table->id();
            $table->string('no_ba')->unique();
            $table->enum('tipe', ['rapat', 'complaint_produksi'])->default('complaint_produksi');
            $table->date('tanggal');
            $table->string('no_surat_jalan_invoice')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('item_product')->nullable();
            $table->json('foto_bukti')->nullable();
            $table->text('kronologi')->nullable();
            $table->text('analisa_produksi')->nullable();
            $table->text('tindakan_solusi')->nullable();
            $table->text('peserta')->nullable();
            $table->text('agenda')->nullable();
            $table->text('catatan')->nullable();
            $table->string('ttd_qc')->nullable();
            $table->string('ttd_customer')->nullable();
            $table->enum('status', ['draft', 'proses', 'selesai'])->default('draft');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berita_acaras');
    }
};