<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('po_bill_pays', function (
            Blueprint $table
        ) {

            $table->id();

            $table->string(
                'kode_pembayaran'
            )->unique();

            $table->foreignId(
                'po_billing_id'
            )->constrained(
                'po_billings'
            );

            $table->foreignId(
                'karyawan_id'
            )->nullable()
             ->constrained(
                 'karyawans'
             );

            $table->date(
                'tanggal_pembayaran'
            );

            $table->string(
                'gudang'
            )->nullable();

            $table->string(
                'periode'
            )->nullable();

            $table->decimal(
                'total_tagihan',
                18,
                2
            )->default(0);

            $table->decimal(
                'total_pembayaran',
                18,
                2
            )->default(0);

            $table->text(
                'keterangan'
            )->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'po_bill_pays'
        );
    }
};