<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerimaan_barang_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('penerimaan_barang_id')
                ->constrained('penerimaan_barangs')
                ->cascadeOnDelete();

            $table->foreignId('purchase_order_item_id')
                ->constrained('purchase_order_items')
                ->cascadeOnDelete();

            $table->decimal('qty_terima', 15, 2)->default(0);

            $table->date('tgl_exp')->nullable();
            $table->string('no_lot')->nullable();

            $table->text('catatan_item')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerimaan_barang_items');
    }
};