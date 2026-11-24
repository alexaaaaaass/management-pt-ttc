<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('printings') && ! Schema::hasColumn('printings', 'satuan_hasil')) {
            Schema::table('printings', function (Blueprint $table) {
                $table->string('satuan_hasil', 20)->default('m')->after('status_laporan');
            });
        }

        if (Schema::hasTable('diemakings') && ! Schema::hasColumn('diemakings', 'satuan_hasil')) {
            Schema::table('diemakings', function (Blueprint $table) {
                $table->string('satuan_hasil', 20)->default('lbr')->after('status_laporan');
            });
        }

        if (Schema::hasTable('master_finishing') && ! Schema::hasColumn('master_finishing', 'satuan_hasil')) {
            Schema::table('master_finishing', function (Blueprint $table) {
                $table->string('satuan_hasil', 20)->default('pcs')->after('status_laporan');
            });
        }

        if (Schema::hasTable('packagings')) {
            DB::statement("ALTER TABLE packagings MODIFY COLUMN jenis_transfer VARCHAR(50) NOT NULL DEFAULT 'barang_hasil_baik'");
        }
    }

    public function down(): void
    {
        foreach (['printings', 'diemakings', 'master_finishing'] as $tbl) {
            if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'satuan_hasil')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->dropColumn('satuan_hasil');
                });
            }
        }
    }
};
