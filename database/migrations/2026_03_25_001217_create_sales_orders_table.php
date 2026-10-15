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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('itemable_type', 255)->nullable();
            $table->unsignedBigInteger('itemable_id')->nullable();
            $table->bigInteger('material_item_id')->nullable();
            $table->string('kode_material', 100)->nullable();
            $table->foreignId('finish_good_item_id')->nullable()->constrained('finish_good_items')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('no_sales_order', 255)->unique();
            $table->string('no_po_customer', 255)->nullable();
            $table->decimal('qty', 15, 2)->nullable();
            $table->decimal('harga_pcs', 15, 2)->nullable();
            $table->decimal('harga_kirim', 15, 2)->nullable();
            $table->string('mata_uang', 50)->nullable();
            $table->string('syarat_pembayaran', 255)->nullable();
            $table->date('tanggal_po')->nullable();
            $table->enum('klaim_kertas', ['claim', 'not_claim'])->default('not_claim')->nullable();
            $table->enum('dipesan_via', ['whatsapp', 'email', 'sosmed'])->nullable();
            $table->string('tipe_pesanan', 255)->nullable();
            $table->decimal('toleransi_pengiriman', 5, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->text('catatan_colour_range')->nullable();
            $table->softDeletes();
            $table->enum('order_type', ['SPK', 'MATERIAL'])->default('SPK');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
