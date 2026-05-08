<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_coas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('karyawan_id')->constrained('karyawans')->cascadeOnDelete();
            $table->foreignId('coa_class_id')->constrained('coa_classes')->cascadeOnDelete();

            $table->string('periode'); // contoh: 2026-04
            $table->string('gudang')->nullable();

            $table->string('kode_akun', 50);
            $table->string('nama_akun', 100);

            $table->decimal('saldo_debit', 15, 2)->default(0);
            $table->decimal('saldo_kredit', 15, 2)->default(0);
            $table->decimal('nominal_default', 15, 2)->default(0);

            $table->text('keterangan')->nullable();

            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_coas');
    }
};