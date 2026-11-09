
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('imr_finish_items')) {
            Schema::create('imr_finish_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('imr_id')
                    ->constrained('imr_finishes')
                    ->cascadeOnDelete();

                $table->foreignId('item_id')
                    ->constrained('master_items');

                $table->decimal('total_pesanan', 15, 2)->default(0);
                $table->decimal('qty_request', 15, 2)->default(0);
                $table->decimal('qty_approved', 15, 2)->default(0);
                $table->decimal('qty_issued', 15, 2)->default(0);
                $table->decimal('qty_returned', 15, 2)->default(0);
                $table->string('satuan')->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('imr_finish_items');
    }
};