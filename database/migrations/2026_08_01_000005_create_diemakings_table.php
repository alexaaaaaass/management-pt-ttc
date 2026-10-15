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
        Schema::create('diemakings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spk_id')->constrained('master_spk')->onDelete('cascade');
            $table->foreignId('mesin_id')->constrained('mesin_die_makings')->onDelete('cascade');
            $table->foreignId('operator_id')->constrained('operator_diemaking')->onDelete('cascade');
            $table->date('tanggal_entri');
            $table->enum('proses_diemaking', ['UV Varnish', 'Hot Print', 'UV Spot', 'UV Holo', 'Embos', 'Cutting']);
            $table->enum('tahap_diemaking', ['Proses Die Making 1', 'Proses Die Making 2']);
            $table->integer('hasil_baik')->default(0);
            $table->integer('hasil_rusak')->default(0);
            $table->integer('semi_waste')->default(0);
            $table->enum('note_waste', ['CETAK KOTOR', 'CETAK LUNTUR', 'CETAK BINTIK'])->nullable();
            $table->enum('keterangan_spk', ['Reguler', 'Subcount']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diemakings');
    }
};
