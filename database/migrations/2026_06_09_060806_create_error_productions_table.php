<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('error_productions', function (Blueprint $table) {
            $table->id();
            $table->string('kode_error')->unique();
            $table->string('nama_error');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('error_productions');
    }
};