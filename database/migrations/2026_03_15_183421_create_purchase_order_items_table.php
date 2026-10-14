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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_order_id')
      ->references('id')
      ->on('purchase_orders')
      ->cascadeOnDelete();
    $table->foreignId('item_id')->constrained('master_items');

            $table->integer('qty_pr')->nullable();
            $table->integer('qty_po')->nullable();
            $table->integer('qty_konversi')->nullable();
            $table->decimal('price', 15, 2)->default(0.00);
            $table->decimal('discount', 5, 2)->default(0.00);
            $table->decimal('total', 15, 2)->default(0.00);
            $table->string('satuan')->nullable();
            $table->date('eta')->nullable();
            $table->text('catatan')->nullable();
            $table->decimal('qty_received', 18, 2)->default(0.00);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};