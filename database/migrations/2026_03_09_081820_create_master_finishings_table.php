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
        Schema::create('master_finishings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mesin');
            $table->string('jenis_mesin');
            $table->integer('kapasitas')->nullable();
            $table->string('proses')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_finishings');
    }
};