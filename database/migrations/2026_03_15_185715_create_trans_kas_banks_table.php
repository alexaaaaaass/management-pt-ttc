<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trans_kas_banks', function (Blueprint $table) {

            $table->id();

            $table->string('kode_transaksi')->nullable();

            $table->enum('tipe_transaksi', [
                'BANK_MASUK',
                'BANK_KELUAR'
            ]);

            $table->string('gudang')->nullable();

            $table->date('tanggal_transaksi');

            $table->string('periode')->nullable();

            $table->foreignId('account_bank_id')
                ->nullable();

            $table->foreignId('account_lawan_id')
                ->nullable();

            $table->bigInteger('nominal')
                ->default(0);

            $table->foreignId('customer_id')
                ->nullable();

            $table->string('bank')
                ->nullable();

            $table->string('atas_nama')
                ->nullable();

            $table->string('no_rekening')
                ->nullable();

            $table->text('keterangan')
                ->nullable();

            $table->enum('status', [
                'ACTIVE',
                'INACTIVE'
            ])->default('ACTIVE');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trans_kas_banks');
    }
};