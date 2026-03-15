<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_request_id')
                  ->constrained('purchase_requests')
                  ->cascadeOnDelete();

            $table->foreignId('item_id')
                  ->constrained('master_items');

            $table->decimal('qty',12,2)->default(0);

            $table->string('satuan')->nullable();

            $table->date('eta')->nullable();

            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_request_items');
    }
};