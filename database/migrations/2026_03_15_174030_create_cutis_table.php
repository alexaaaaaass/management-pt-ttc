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
    Schema::create('cutis', function (Blueprint $table) {
    $table->id();

    $table->foreignId('karyawan_id')
        ->constrained('karyawans')
        ->cascadeOnDelete();

    $table->date('tanggal_cuti');

    $table->string('jenis_cuti');

    $table->integer('jumlah_hari')->default(1);

    $table->string('lampiran')->nullable();

    $table->text('keterangan')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cutis');
    }
};