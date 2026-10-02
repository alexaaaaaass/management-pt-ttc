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
        Schema::create('imr_printing_items', function (Blueprint $table) {
           $table->id();

            $table->foreignId('imr_id')
                ->constrained('imr_printing')
                ->cascadeOnDelete();

            $table->foreignId('item_id')
                ->constrained('master_items');

            $table->decimal('total_pesanan',15,2);

            $table->decimal('qty_request',15,2)
                ->default(0);

            $table->decimal('qty_approved',15,2)
                ->default(0);

            $table->decimal('qty_issued',15,2)
                ->default(0);

            $table->decimal('qty_returned',15,2)
                ->default(0);

            $table->string('satuan')->nullable();

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