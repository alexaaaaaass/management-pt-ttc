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
        Schema::create('pemb_pinjamans', function (Blueprint $table) {
            $table->id();
            $table->string('no_bukti', 50)->unique();
            $table->foreignId('peng_pinjaman_id')->constrained('peng_pinjamans')->onDelete('cascade');
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->integer('tahap_cicilan');
            $table->date('tanggal_pembayaran');
            $table->decimal('nominal_pembayaran', 15, 2)->default(0.00);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemb_pinjamans');
    }
};
