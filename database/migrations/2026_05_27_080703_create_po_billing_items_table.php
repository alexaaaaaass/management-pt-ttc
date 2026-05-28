<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('po_billing_items', function (
            Blueprint $table
        ) {

            $table->id();

            $table->foreignId('po_billing_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('item_id');

            $table->string('item_name');

            $table->decimal('qty', 18, 2);

            $table->decimal('harga', 18, 2);

            $table->decimal(
                'diskon',
                5,
                2
            )->default(0);

            $table->decimal(
                'subtotal',
                18,
                2
            );

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'po_billing_items'
        );
    }
};