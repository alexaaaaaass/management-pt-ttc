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
        Schema::create('subcount_ins', function (Blueprint $table) {
            $table->id();
            $table->string('no_subcount', 50)->unique()->nullable();
            $table->date('tanggal_subcount');
            $table->string('surat_jalan_pengiriman', 100)->unique()->nullable();
            $table->string('admin_produksi', 100)->nullable();
            $table->string('supervisor', 100)->nullable();
            $table->string('admin_mainstore', 100)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcount_ins');
    }
};
