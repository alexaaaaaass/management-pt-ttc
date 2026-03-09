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
    Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->string('kode_customer')->unique();
        $table->string('nama_customer');

        $table->text('alamat_lengkap');
        $table->text('alamat_kedua')->nullable();
        $table->text('alamat_ketiga')->nullable();

        $table->string('kode_group');
        $table->string('nama_group');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};