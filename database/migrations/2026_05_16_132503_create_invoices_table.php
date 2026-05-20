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

            $table->foreignId('surat_jalan_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('tanggal_invoice');

            $table->date('tanggal_jatuh_tempo');

            $table->double('subtotal')->default(0);

            $table->double('diskon')->default(0);

            $table->double('ppn')->default(0);

            $table->double('ongkir')->default(0);

            $table->double('uang_muka')->default(0);

            $table->double('grand_total')->default(0);

            $table->double('sisa_tagihan')->default(0);

            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};