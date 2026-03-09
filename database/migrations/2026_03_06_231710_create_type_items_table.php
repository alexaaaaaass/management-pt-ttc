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
        Schema::create('type_items', function (Blueprint $table) {
            $table->id();
            $table->string('kode_type_item')->unique();
            $table->string('nama_type_item');

            $table->foreignId('category_item_id')
                  ->constrained('category_items')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_items');
    }
};