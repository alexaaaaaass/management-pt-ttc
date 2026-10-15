<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('kode_invoice')->unique();
            $table->foreignId('surat_jalan_id')->constrained('surat_jalans')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders');
            $table->string('no_po_customer', 100)->nullable();
            $table->date('tanggal_invoice');
            $table->date('tanggal_jatuh_tempo');
            $table->bigInteger('subtotal')->default(0);
            $table->decimal('diskon', 5, 2)->default(0.00);
            $table->decimal('ppn', 5, 2)->default(0.00);
            $table->bigInteger('ongkir')->default(0);
            $table->bigInteger('uang_muka')->default(0);
            $table->bigInteger('grand_total')->default(0);
            $table->decimal('total_payment', 18, 2)->default(0.00);
            $table->bigInteger('sisa_tagihan')->default(0);
            $table->enum('status', ['Draft', 'Unpaid', 'Partial', 'Paid', 'Cancelled'])->default('Draft');
            $table->boolean('is_printed')->default(false);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};