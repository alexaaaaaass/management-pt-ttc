<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berita_acara_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('berita_acara_id')->constrained('berita_acaras')->cascadeOnDelete();
            $table->string('item_product')->nullable();
            $table->decimal('qty_kedatangan', 15, 2)->nullable();
            $table->decimal('qty_penerimaan', 15, 2)->nullable();
            $table->decimal('qty_penolakan', 15, 2)->nullable();
            $table->text('catatan_penerimaan')->nullable();
            $table->string('foto_referensi')->nullable();
            $table->string('foto_fisik')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berita_acara_items');
    }
};