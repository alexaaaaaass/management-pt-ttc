<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
      Schema::create('trans_faktur_items', function (Blueprint $table) {

    $table->id();

    $table->foreignId('trans_faktur_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->string('deskripsi');

    $table->decimal('qty', 18, 2)
        ->default(0);

    $table->string('unit')
        ->nullable();

    $table->decimal('harga_satuan', 18, 2)
        ->default(0);

    $table->decimal('diskon', 18, 2)
        ->default(0);

    $table->decimal('total', 18, 2)
        ->default(0);

    $table->text('catatan')
        ->nullable();

    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('trans_faktur_items');
    }
};