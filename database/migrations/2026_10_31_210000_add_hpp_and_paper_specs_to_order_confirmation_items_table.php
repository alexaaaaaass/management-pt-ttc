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
        Schema::table('order_confirmation_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_confirmation_items', 'hpp_per_pcs')) {
                $table->decimal('hpp_per_pcs', 12, 2)->nullable()->after('total');
            }
            if (!Schema::hasColumn('order_confirmation_items', 'harga_jual_indikatif')) {
                $table->decimal('harga_jual_indikatif', 12, 2)->nullable()->after('hpp_per_pcs');
            }
            if (!Schema::hasColumn('order_confirmation_items', 'total_biaya_order')) {
                $table->decimal('total_biaya_order', 15, 2)->nullable()->after('harga_jual_indikatif');
            }
            if (!Schema::hasColumn('order_confirmation_items', 'biaya_bahan')) {
                $table->decimal('biaya_bahan', 15, 2)->nullable()->after('total_biaya_order');
            }
            if (!Schema::hasColumn('order_confirmation_items', 'biaya_proses')) {
                $table->decimal('biaya_proses', 15, 2)->nullable()->after('biaya_bahan');
            }
            if (!Schema::hasColumn('order_confirmation_items', 'kebutuhan_kertas_kg')) {
                $table->decimal('kebutuhan_kertas_kg', 10, 2)->nullable()->after('biaya_proses');
            }
            if (!Schema::hasColumn('order_confirmation_items', 'kebutuhan_kertas_meter')) {
                $table->decimal('kebutuhan_kertas_meter', 10, 2)->nullable()->after('kebutuhan_kertas_kg');
            }
            if (!Schema::hasColumn('order_confirmation_items', 'kalkulasi_hpp_data')) {
                $table->json('kalkulasi_hpp_data')->nullable()->after('kebutuhan_kertas_meter');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_confirmation_items', function (Blueprint $table) {
            $table->dropColumn([
                'hpp_per_pcs',
                'harga_jual_indikatif',
                'total_biaya_order',
                'biaya_bahan',
                'biaya_proses',
                'kebutuhan_kertas_kg',
                'kebutuhan_kertas_meter',
                'kalkulasi_hpp_data',
            ]);
        });
    }
};

