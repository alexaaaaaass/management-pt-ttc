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
        Schema::create('sales_order_items', function (Blueprint $table) {

    $table->id();

    $table->foreignId('sales_order_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('master_item_id')
        ->constrained('master_items');

    $table->decimal('qty',15,2);

    $table->decimal('harga',15,2);

    $table->decimal('subtotal',15,2)->default(0);

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