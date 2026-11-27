<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            if (! Schema::hasColumn('karyawans', 'masa_kontrak')) {
                $table->string('masa_kontrak', 30)->nullable()->after('tanggal_masuk_kerja');
            }
            if (! Schema::hasColumn('karyawans', 'tanggal_mulai_kontrak')) {
                $table->date('tanggal_mulai_kontrak')->nullable()->after('masa_kontrak');
            }
        });

        if (Schema::hasTable('hr_payroll_settings')) {
            Schema::table('hr_payroll_settings', function (Blueprint $table) {
                if (! Schema::hasColumn('hr_payroll_settings', 'alert_kontrak_hari')) {
                    $table->unsignedSmallInteger('alert_kontrak_hari')->default(30)->after('kuota_cuti_tahunan');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            if (Schema::hasColumn('karyawans', 'tanggal_mulai_kontrak')) {
                $table->dropColumn('tanggal_mulai_kontrak');
            }
            if (Schema::hasColumn('karyawans', 'masa_kontrak')) {
                $table->dropColumn('masa_kontrak');
            }
        });

        if (Schema::hasTable('hr_payroll_settings') && Schema::hasColumn('hr_payroll_settings', 'alert_kontrak_hari')) {
            Schema::table('hr_payroll_settings', function (Blueprint $table) {
                $table->dropColumn('alert_kontrak_hari');
            });
        }
    }
};
