<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('printing_wastes')) {
            Schema::create('printing_wastes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('printing_id')
                    ->constrained('printings')
                    ->cascadeOnDelete();
                $table->foreignId('error_production_id')
                    ->constrained('error_productions')
                    ->cascadeOnDelete();
                $table->decimal('qty_waste', 12, 2)->default(0);
                $table->string('catatan')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('printing_wastes');
    }
};
