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
        Schema::table('master_items', function (Blueprint $table) {
            $table->integer('qty')->nullable();
            $table->decimal('panjang', 10, 2)->nullable();
            $table->decimal('lebar', 10, 2)->nullable();
            $table->decimal('tinggi', 10, 2)->nullable();
            $table->decimal('berat', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_items', function (Blueprint $table) {
            $table->dropColumn(['qty', 'panjang', 'lebar', 'tinggi', 'berat']);
        });
    }
};