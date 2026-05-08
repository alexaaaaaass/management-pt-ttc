<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pracetak_processes', function (Blueprint $table) {
            $table->id();

            // 🔥 FK (AUTO UNSIGNED)
            $table->foreignId('spk_id')->constrained('master_spk')->cascadeOnDelete();

            $table->integer('qty');

            $table->enum('status', ['design', 'plat_making', 'selesai'])
                  ->default('design');

            $table->enum('ket_spk', ['reguler', 'subcount'])
                  ->default('reguler');

            $table->string('nama_plat');

            // 🔥 hasil produksi
            $table->integer('hasil_baik')->nullable();
            $table->integer('hasil_rusak')->nullable();

            $table->date('tgl_entry')->nullable();

            // 🔥 FK operator & mesin
            $table->foreignId('operator_id')
                  ->nullable()
                  ->constrained('operator_pracetak')
                  ->nullOnDelete();

            $table->foreignId('mesin_id')
                  ->nullable()
                  ->constrained('mesin_pra_cetaks')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pracetak_processes');
    }
};