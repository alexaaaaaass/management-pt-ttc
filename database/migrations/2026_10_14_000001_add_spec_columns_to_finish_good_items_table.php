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
        Schema::table('finish_good_items', function (Blueprint $table) {
            $table->string('jenis', 100)->nullable()->after('nama_barang');
            $table->string('komponen', 100)->nullable()->after('jenis');
            $table->string('isi', 50)->nullable()->after('komponen');
            $table->string('warna', 50)->nullable()->after('tebal_kertas');
            $table->string('finishing', 255)->nullable()->after('warna');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finish_good_items', function (Blueprint $table) {
            $table->dropColumn(['jenis', 'komponen', 'isi', 'warna', 'finishing']);
        });
    }
};
