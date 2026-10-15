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
        Schema::create('master_finishing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spk_id')->constrained('master_spk')->onDelete('cascade');
            $table->string('kode_finishing', 100)->nullable();
            $table->foreignId('mesin_finishing_id')->constrained('mesin_finishing')->onDelete('cascade');
            $table->foreignId('operator_finishing_id')->constrained('operator_finishings')->onDelete('cascade');
            $table->date('tanggal_entri');
            $table->enum('proses_finishing', ['protol', 'sorter', 'lem']);
            $table->enum('tahap_finishing', ['reguler', 'blokir', 'semi_waste', 'retur']);
            $table->integer('hasil_baik')->default(0)->nullable();
            $table->integer('hasil_rusak')->default(0)->nullable();
            $table->integer('semi_waste')->default(0)->nullable();
            $table->foreignId('error_production_id')->nullable()->constrained('error_productions')->onDelete('set null');
            $table->enum('keterangan_spk', ['reguler', 'subcount']);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_finishing');
    }
};
