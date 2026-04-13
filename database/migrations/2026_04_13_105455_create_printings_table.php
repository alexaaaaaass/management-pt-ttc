<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('printings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('spk_id')
                ->constrained('master_spk')
                ->cascadeOnDelete();

            $table->foreignId('mesin_id')
                ->constrained('master_mesin_prints')
                ->cascadeOnDelete();

            $table->foreignId('operator_id')
                ->constrained('master_operators')
                ->cascadeOnDelete();

            $table->date('tanggal_entri');

            $table->enum('proses_printing', [
                'potong',
                'printing'
            ]);

            $table->enum('tahap_printing', [
                'potong',
                'proses_cetak',
                'proses_cetak_2'
            ]);

            $table->integer('hasil_baik')->default(0);
            $table->integer('hasil_rusak')->default(0);
            $table->integer('semi_waste')->default(0);

            $table->enum('note_waste', [
                'CETAK_LUNTUR',
                'CETAK_BOCOR_BANJIR'
            ])->nullable();

            $table->string('keterangan_spk');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('printings');
    }
};