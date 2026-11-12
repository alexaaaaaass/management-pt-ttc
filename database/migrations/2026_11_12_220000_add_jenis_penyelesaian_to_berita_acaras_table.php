<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('berita_acaras') && ! Schema::hasColumn('berita_acaras', 'jenis_penyelesaian')) {
            Schema::table('berita_acaras', function (Blueprint $table) {
                $table->string('jenis_penyelesaian', 50)->nullable()->after('analisa_produksi');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('berita_acaras') && Schema::hasColumn('berita_acaras', 'jenis_penyelesaian')) {
            Schema::table('berita_acaras', function (Blueprint $table) {
                $table->dropColumn('jenis_penyelesaian');
            });
        }
    }
};
