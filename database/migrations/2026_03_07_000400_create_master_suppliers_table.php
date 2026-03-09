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
        Schema::create('master_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('kode_supplier')->unique();
            $table->string('nama_supplier');
            $table->string('jenis_supplier')->nullable();
            $table->text('alamat')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_suppliers');
    }
};