<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
      Schema::create('master_items', function (Blueprint $table) {
    $table->id();

    $table->string('kode_master_item')->unique();
    $table->string('nama_master_item');

    $table->integer('min_stock')->nullable();
    $table->integer('min_order')->nullable();

    $table->foreignId('satuan_id')->constrained('master_satuans');
    $table->foreignId('category_item_id')->constrained('category_items');
    $table->foreignId('type_item_id')->constrained('type_items');

    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('master_items');
    }
};