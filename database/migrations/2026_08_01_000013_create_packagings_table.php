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
        Schema::create('packagings', function (Blueprint $table) {
            $table->id();
            $table->string('kode_packaging', 100)->nullable();
            $table->foreignId('spk_id')->constrained('master_spk')->onDelete('cascade');
            $table->enum('satuan_transfer', ['Box', 'Pallete', 'Pack']);
            $table->enum('jenis_transfer', ['barang_hasil_baik', 'label_kuning', 'blokir']);
            $table->date('tgl_transfer');
            $table->integer('jumlah_satuan_penuh')->default(0)->nullable();
            $table->integer('qty_per_satuan_penuh')->default(0)->nullable();
            $table->integer('jumlah_satuan_sisa')->default(0)->nullable();
            $table->integer('qty_per_satuan_sisa')->default(0)->nullable();
            $table->bigInteger('total_satuan_penuh')->default(0)->nullable();
            $table->bigInteger('total_satuan_sisa')->default(0)->nullable();
            $table->bigInteger('grand_total')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packagings');
    }
};
