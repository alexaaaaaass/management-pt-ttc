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
        Schema::create('coa_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coa_id')->constrained('master_coas')->onDelete('cascade');
            $table->date('tanggal');
            $table->string('reference_type', 50); // TRANS_KAS, TRANS_BANK, OPERASIONAL_PAY, INV_PAYMENT, PO_BILL_PAY
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_no', 50)->nullable();
            $table->string('movement_type', 30); // DEBIT, KREDIT
            $table->decimal('nominal', 18, 2)->default(0.00);
            $table->decimal('debit', 18, 2)->default(0.00);
            $table->decimal('kredit', 18, 2)->default(0.00);
            $table->decimal('balance_before', 18, 2)->default(0.00);
            $table->decimal('balance_after', 18, 2)->default(0.00);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['coa_id', 'tanggal', 'id']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coa_movements');
    }
};
