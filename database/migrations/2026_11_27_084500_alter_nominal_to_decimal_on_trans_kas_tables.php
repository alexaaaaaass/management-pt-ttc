<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('trans_kas') && Schema::hasColumn('trans_kas', 'nominal')) {
            DB::statement('ALTER TABLE `trans_kas` MODIFY `nominal` DECIMAL(18,2) NOT NULL DEFAULT 0.00');
        }

        if (Schema::hasTable('trans_kas_banks') && Schema::hasColumn('trans_kas_banks', 'nominal')) {
            DB::statement('ALTER TABLE `trans_kas_banks` MODIFY `nominal` DECIMAL(18,2) NOT NULL DEFAULT 0.00');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('trans_kas') && Schema::hasColumn('trans_kas', 'nominal')) {
            DB::statement('ALTER TABLE `trans_kas` MODIFY `nominal` BIGINT(20) NOT NULL DEFAULT 0');
        }

        if (Schema::hasTable('trans_kas_banks') && Schema::hasColumn('trans_kas_banks', 'nominal')) {
            DB::statement('ALTER TABLE `trans_kas_banks` MODIFY `nominal` BIGINT(20) NOT NULL DEFAULT 0');
        }
    }
};
