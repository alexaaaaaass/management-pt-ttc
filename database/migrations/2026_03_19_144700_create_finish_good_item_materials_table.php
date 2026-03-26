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
      Schema::create('finish_good_item_materials', function (Blueprint $table) {
    $table->id();

    $table->foreignId('finish_good_item_id')
        ->constrained()
        ->cascadeOnDelete(); // 🔥 penting biar ikut kehapus

    $table->foreignId('master_item_id')
        ->nullable()
        ->constrained('master_items')
        ->nullOnDelete();

    $table->foreignId('departemen_id')
        ->nullable()
        ->constrained('master_departemens')
        ->nullOnDelete();

    $table->integer('qty')->nullable();
    $table->decimal('waste', 10, 2)->default(0);
    $table->text('keterangan')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finish_good_item_materials');
    }
};