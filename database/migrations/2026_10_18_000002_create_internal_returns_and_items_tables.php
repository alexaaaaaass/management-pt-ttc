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
        if (! Schema::hasTable('internal_returns')) {
            Schema::create('internal_returns', function (Blueprint $table) {
                $table->id();
                $table->string('no_return')->unique();
                $table->foreignId('imr_id')
                    ->constrained('imr_printing')
                    ->cascadeOnDelete();
                $table->date('tanggal_return');
                $table->text('alasan')->nullable();
                $table->enum('status', ['draft', 'approved', 'cancel'])->default('draft');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('internal_return_items')) {
            Schema::create('internal_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('internal_return_id')
                    ->constrained('internal_returns')
                    ->cascadeOnDelete();
                $table->foreignId('imr_printing_item_id')
                    ->constrained('imr_printing_items')
                    ->cascadeOnDelete();
                $table->foreignId('item_id')
                    ->nullable()
                    ->constrained('master_items')
                    ->nullOnDelete();
                $table->decimal('qty_return', 15, 2)->default(0);
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_return_items');
        Schema::dropIfExists('internal_returns');
    }
};
