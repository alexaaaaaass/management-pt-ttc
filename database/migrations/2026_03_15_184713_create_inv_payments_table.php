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
      Schema::create('inv_payments', function (Blueprint $table) {

    $table->id();

    $table->foreignId('invoice_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('metode_bayar_id')
        ->constrained('metode_bayars');

    $table->foreignId('coa_id')
        ->constrained('master_coas');

    $table->foreignId('karyawan_id')
        ->constrained('karyawans');

    $table->date('tanggal_bayar');

    $table->double('nominal_bayar')->default(0);

    $table->text('keterangan')->nullable();

    $table->string('status')->default('PAID');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_payments');
    }
};