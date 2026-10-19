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
            if (! Schema::hasColumn('finish_good_items', 'kertas_items')) {
                $table->json('kertas_items')->nullable()->after('spesifikasi_kertas');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finish_good_items', function (Blueprint $table) {
            if (Schema::hasColumn('finish_good_items', 'kertas_items')) {
                $table->dropColumn('kertas_items');
            }
        });
    }
};
