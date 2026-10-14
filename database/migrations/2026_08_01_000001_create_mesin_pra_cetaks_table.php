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
        Schema::create('mesin_pra_cetaks', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mesin', 255);
            $table->string('jenis_mesin', 255);
            $table->string('kapasitas', 100)->nullable();
            $table->string('proses', 100);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mesin_pra_cetaks');
    }
};
