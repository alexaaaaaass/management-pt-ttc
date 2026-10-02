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
        Schema::create('external_return_items', function (Blueprint $table) {

    $table->id();

    $table->foreignId('external_return_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('penerimaan_barang_item_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->decimal('qty_return',15,2);

    $table->text('catatan')->nullable();

    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};