<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operasional_pays', function (Blueprint $table) {

            $table->id();

            $table->string('kode_transaksi')
                ->nullable();

            $table->string('gudang')
                ->nullable();

            $table->string('periode')
                ->nullable();

            $table->date('tanggal_transaksi');

            $table->foreignId('account_kas_id')
                ->nullable();

            $table->foreignId('account_beban_id')
                ->nullable();

            $table->foreignId('karyawan_id')
                ->nullable();

            $table->bigInteger('nominal')
                ->default(0);

            $table->string('nopol')
                ->nullable();

            $table->string('odometer')
                ->nullable();

            $table->string('mesin')
                ->nullable();

            $table->string('jenis')
                ->nullable();

            $table->string('kode')
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
        Schema::dropIfExists('operasional_pays');
    }
};