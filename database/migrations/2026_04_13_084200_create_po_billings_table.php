<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('po_billings', function (
            Blueprint $table
        ) {

            $table->id();

            $table->string('kode_tagihan')->unique();

            $table->foreignId('penerimaan_barang_id')
                ->constrained('penerimaan_barangs')
                ->cascadeOnDelete();

            $table->foreignId('purchase_order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('supplier_id')
                ->constrained('master_suppliers')
                ->cascadeOnDelete();

            $table->foreignId('karyawan_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('invoice_vendor')
                ->nullable();

            $table->year('periode');

            $table->date('tanggal_transaksi')
                ->nullable();

            $table->date('tanggal_jatuh_tempo')
                ->nullable();

            $table->text('keterangan')
                ->nullable();

            $table->decimal(
                'total_barang',
                18,
                2
            )->default(0);

            $table->decimal(
                'ppn',
                18,
                2
            )->default(0);

            $table->decimal(
                'ongkir',
                18,
                2
            )->default(0);

            $table->decimal(
                'dp',
                18,
                2
            )->default(0);

            $table->decimal(
                'grand_total',
                18,
                2
            )->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('po_billings');
    }
};