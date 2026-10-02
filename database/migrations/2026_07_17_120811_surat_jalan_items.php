<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_jalan_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('surat_jalan_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('sales_order_item_id')
                ->constrained('sales_order_items')
                ->cascadeOnDelete();

            $table->foreignId('master_item_id')
                ->constrained('master_items');

            $table->decimal('qty_kirim',18,2)
                ->default(0);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_jalan_items');
    }
};