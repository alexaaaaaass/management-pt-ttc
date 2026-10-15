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
        Schema::create('subcount_outs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_subcount');
            $table->string('surat_jalan_subcount', 50)->unique()->nullable();
            $table->foreignId('supplier_id')->constrained('master_suppliers');
            $table->string('admin_produksi', 255)->nullable();
            $table->string('supervisor', 255)->nullable();
            $table->string('admin_mainstore', 255)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcount_outs');
    }
};
