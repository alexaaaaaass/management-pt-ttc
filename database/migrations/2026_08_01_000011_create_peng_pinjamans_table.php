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
        Schema::create('peng_pinjamans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_bukti', 50)->unique();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->string('kode_gudang', 50);
            $table->date('tanggal_pengajuan');
            $table->decimal('nilai_pinjaman', 15, 2)->default(0.00);
            $table->integer('jangka_waktu')->comment('Dalam bulan');
            $table->decimal('cicilan_per_bulan', 15, 2)->default(0.00);
            $table->text('keperluan_pinjaman')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peng_pinjamans');
    }
};
