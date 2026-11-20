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
        Schema::create('external_returns', function (Blueprint $table) {

            $table->id();

            $table->string('no_return')->unique();

            $table->foreignId('penerimaan_barang_id')
                ->constrained('penerimaan_barangs')
                ->cascadeOnDelete();

            $table->date('tanggal_return');

            $table->foreignId('supplier_id')
                ->constrained('master_suppliers');

            $table->text('alasan')->nullable();

          $table->enum('status',[
    'draft',
    'approved',
    'cancel'
])->default('draft');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_returns');
    }
};