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
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->nullable()->constrained('karyawans')->onDelete('set null');
            $table->string('nip', 50)->nullable();
            $table->string('pin', 50)->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->string('departemen', 100)->nullable();
            $table->string('kantor', 100)->nullable();
            $table->dateTime('tanggal_scan')->nullable();
            $table->string('sn', 100)->nullable();
            $table->string('mesin', 100)->nullable();
            $table->string('workcode', 50)->nullable();
            $table->enum('io', ['Masuk', 'Keluar'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
