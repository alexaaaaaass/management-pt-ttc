<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pracetak_plate_archives') && ! Schema::hasColumn('pracetak_plate_archives', 'qty_rusak')) {
            Schema::table('pracetak_plate_archives', function (Blueprint $table) {
                $table->unsignedInteger('qty_rusak')->default(0)->after('qty_dipakai');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pracetak_plate_archives') && Schema::hasColumn('pracetak_plate_archives', 'qty_rusak')) {
            Schema::table('pracetak_plate_archives', function (Blueprint $table) {
                $table->dropColumn('qty_rusak');
            });
        }
    }
};
