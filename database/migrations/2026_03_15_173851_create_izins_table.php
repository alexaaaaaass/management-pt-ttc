<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('izins', function (Blueprint $table) {
            $table->id();

            $table->foreignId('karyawan_id')
                ->constrained('karyawans')
                ->cascadeOnDelete();

            $table->date('tanggal_izin');

            $table->enum('jenis_izin', [
                'terlambat',
                'meninggalkan_kantor',
                'pulang_awal',
                'dinas_luar',
                'alpha',
            ]);

            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('izins');
    }
};