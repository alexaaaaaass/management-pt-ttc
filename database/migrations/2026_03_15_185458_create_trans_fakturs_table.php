<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
    Schema::create('trans_fakturs', function (Blueprint $table) {

    $table->id();

    $table->foreignId('purchase_order_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('pic_id')
        ->nullable()
        ->constrained('karyawans')
        ->nullOnDelete();

    $table->string('gudang')
        ->nullable();

    $table->string('no_invoice')
        ->nullable();

    $table->string('no_faktur')
        ->nullable();

    $table->date('tanggal_transaksi');

    $table->decimal('subtotal', 18, 2)
        ->default(0);

    $table->decimal('ppn', 18, 2)
        ->default(0);

    $table->decimal('grand_total', 18, 2)
        ->default(0);

    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('trans_fakturs');
    }
};