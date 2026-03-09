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
        Schema::create('master_konversis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('type_item_id')
                ->constrained('type_items')
                ->cascadeOnDelete();

            $table->foreignId('satuan_satu_id')
                ->constrained('master_satuans')
                ->cascadeOnDelete();

            $table->foreignId('satuan_dua_id')
                ->constrained('master_satuans')
                ->cascadeOnDelete();

            $table->integer('nilai_konversi'); // contoh 1 BOX = 12 PCS

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_konversis');
    }
};