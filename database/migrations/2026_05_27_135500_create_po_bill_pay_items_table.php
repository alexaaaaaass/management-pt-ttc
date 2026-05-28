<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'po_bill_pay_items',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId(
                    'po_bill_pay_id'
                )->constrained();

                $table->foreignId(
                    'metode_bayar_id'
                )->nullable()
                 ->constrained(
                     'metode_bayars'
                 );

                $table->foreignId(
                    'coa_debit_id'
                )->nullable()
                 ->constrained(
                     'master_coas'
                 );

                $table->foreignId(
                    'coa_kredit_id'
                )->nullable()
                 ->constrained(
                     'master_coas'
                 );

                $table->decimal(
                    'nominal_pembayaran',
                    18,
                    2
                )->default(0);

                $table->string(
                    'mata_uang'
                )->default('IDR');

                $table->string(
                    'nama_bank'
                )->nullable();

                $table->string(
                    'no_rekening'
                )->nullable();

                $table->string(
                    'atas_nama'
                )->nullable();

                $table->text(
                    'memo'
                )->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'po_bill_pay_items'
        );
    }
};