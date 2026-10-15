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
        Schema::create('subcount_out_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subcount_out_id')->constrained('subcount_outs')->onDelete('cascade');
            $table->enum('sumber_item', ['spk', 'material'])->nullable();
            $table->foreignId('spk_id')->nullable()->constrained('master_spk');
            $table->foreignId('item_id')->nullable()->constrained('master_items');
            $table->decimal('qty', 18, 2)->default(0.00)->nullable();
            $table->foreignId('satuan_id')->nullable()->constrained('master_satuans');
            $table->string('nama_produk', 255)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcount_out_items');
    }
};
