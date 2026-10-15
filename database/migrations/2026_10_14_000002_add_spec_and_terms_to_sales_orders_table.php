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
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('lead_time', 100)->nullable()->default('21 Hari Kerja')->after('tanggal_po');
            $table->decimal('diskon', 15, 2)->default(0)->after('harga_kirim');
            $table->string('jenis', 100)->nullable()->after('toleransi_pengiriman');
            $table->string('komponen', 100)->nullable()->after('jenis');
            $table->string('isi', 50)->nullable()->after('komponen');
            $table->string('kertas', 255)->nullable()->after('isi');
            $table->string('warna', 50)->nullable()->after('kertas');
            $table->text('finishing')->nullable()->after('warna');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn([
                'lead_time',
                'diskon',
                'jenis',
                'komponen',
                'isi',
                'kertas',
                'warna',
                'finishing',
            ]);
        });
    }
};
