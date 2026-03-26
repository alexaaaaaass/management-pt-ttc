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
      Schema::create('finish_good_items', function (Blueprint $table) {
    $table->id();

    // Relasi master
    $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('type_item_id')->nullable()->constrained('type_items')->nullOnDelete();
    $table->foreignId('satuan_id')->nullable()->constrained('master_satuans')->nullOnDelete();

    // Field utama
    $table->string('kode_material_produk')->nullable();
    $table->string('kode_barcode')->nullable();
    $table->string('pc_number')->nullable();
    $table->string('nama_barang')->nullable();
    $table->text('deskripsi')->nullable();
    $table->text('spesifikasi_kertas')->nullable();

    // UP
    $table->string('up_satu')->nullable();
    $table->string('up_dua')->nullable();
    $table->string('up_tiga')->nullable();

    // Ukuran
    $table->string('ukuran_potong')->nullable();
    $table->string('ukuran_cetak')->nullable();
    $table->decimal('panjang', 10, 2)->nullable();
    $table->decimal('lebar', 10, 2)->nullable();
    $table->decimal('tinggi', 10, 2)->nullable();

    // Berat
    $table->string('berat_kotor')->nullable();
    $table->string('berat_bersih')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finish_good_items');
    }
};