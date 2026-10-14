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
        Schema::create('subcount_in_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subcount_in_id')->constrained('subcount_ins')->onDelete('cascade');
            $table->foreignId('subcount_out_id')->constrained('subcount_outs')->onDelete('cascade');
            $table->foreignId('item_hasil_id')->constrained('master_items');
            $table->decimal('qty_diterima', 18, 2)->default(0.00)->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->unsignedBigInteger('item_asal_id')->nullable();
            $table->decimal('qty_dikirim', 18, 2)->default(0.00)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcount_in_items');
    }
};
