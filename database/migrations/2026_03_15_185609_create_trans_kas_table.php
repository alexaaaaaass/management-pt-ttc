<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trans_kas', function (
            Blueprint $table
        ) {

            $table->id();

            $table->string(
                'kode_transaksi'
            )->unique();

            $table->enum(
                'tipe_transaksi',
                [
                    'KAS_MASUK',
                    'KAS_KELUAR'
                ]
            );

            $table->string('gudang')
                ->nullable();

            $table->date(
                'tanggal_transaksi'
            );

            $table->string('periode')
                ->nullable();

            $table->foreignId(
                'karyawan_id'
            )
                ->nullable()
                ->constrained('karyawans')
                ->nullOnDelete();

            $table->foreignId(
                'account_bank_id'
            )
                ->nullable()
                ->constrained('master_coas')
                ->nullOnDelete();

            $table->foreignId(
                'account_kas_id'
            )
                ->nullable()
                ->constrained('master_coas')
                ->nullOnDelete();

            $table->unsignedBigInteger(
                'customer_id'
            )->nullable();

            $table->bigInteger(
                'nominal'
            )->default(0);

            $table->enum(
                'status',
                [
                    'ACTIVE',
                    'INACTIVE'
                ]
            )->default('ACTIVE');

            $table->text(
                'keterangan'
            )->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'trans_kas'
        );
    }
};