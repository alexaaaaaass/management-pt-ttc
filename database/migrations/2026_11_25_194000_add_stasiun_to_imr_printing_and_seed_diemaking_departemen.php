<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('imr_printing') && ! Schema::hasColumn('imr_printing', 'stasiun')) {
            Schema::table('imr_printing', function (Blueprint $table) {
                $table->string('stasiun', 30)
                    ->default('printing')
                    ->after('no_imr')
                    ->index();
            });
        }

        if (Schema::hasTable('master_departemens')) {
            $exists = DB::table('master_departemens')
                ->where('kode', 'DIEM')
                ->orWhere('nama_departemen', 'like', '%Die Making%')
                ->orWhere('nama_departemen', 'like', '%Diemaking%')
                ->exists();

            if (! $exists) {
                DB::table('master_departemens')->insert([
                    'kode'            => 'DIEM',
                    'nama_departemen' => 'Die Making',
                    'status'          => 1,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('imr_printing') && Schema::hasColumn('imr_printing', 'stasiun')) {
            Schema::table('imr_printing', function (Blueprint $table) {
                $table->dropIndex(['stasiun']);
                $table->dropColumn('stasiun');
            });
        }
    }
};
