<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('master_suppliers');
            $table->date('po_date');

            $table->date('eta')->nullable();
            $table->string('currency')->default('IDR');

            $table->decimal('ppn',5,2)->default(0);
            $table->decimal('ongkir',12,2)->default(0);
            $table->decimal('dp',12,2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};