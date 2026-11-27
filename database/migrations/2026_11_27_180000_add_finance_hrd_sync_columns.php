<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom integrasi Finance pada tabel gajis
        if (Schema::hasTable('gajis')) {
            Schema::table('gajis', function (Blueprint $table) {
                if (! Schema::hasColumn('gajis', 'bpjs_perusahaan')) {
                    $table->decimal('bpjs_perusahaan', 15, 2)->default(0)->after('potongan_bpjs');
                }
                if (! Schema::hasColumn('gajis', 'coa_beban_id')) {
                    $table->unsignedBigInteger('coa_beban_id')->nullable()->after('account_kas_id');
                }
                if (! Schema::hasColumn('gajis', 'npb_request_id')) {
                    $table->unsignedBigInteger('npb_request_id')->nullable()->after('coa_beban_id');
                }
            });
        }

        // 2. Tambah kolom integrasi Finance pada payroll_borongans
        if (Schema::hasTable('payroll_borongans')) {
            Schema::table('payroll_borongans', function (Blueprint $table) {
                if (! Schema::hasColumn('payroll_borongans', 'coa_beban_id')) {
                    $table->unsignedBigInteger('coa_beban_id')->nullable()->after('account_kas_id');
                }
                if (! Schema::hasColumn('payroll_borongans', 'npb_request_id')) {
                    $table->unsignedBigInteger('npb_request_id')->nullable()->after('coa_beban_id');
                }
            });
        }

        // 3. Tambah kolom pencairan & posting GL pada peng_pinjamans (Kasbon Karyawan)
        if (Schema::hasTable('peng_pinjamans')) {
            Schema::table('peng_pinjamans', function (Blueprint $table) {
                if (! Schema::hasColumn('peng_pinjamans', 'account_kas_id')) {
                    $table->unsignedBigInteger('account_kas_id')->nullable()->after('catatan_approval');
                }
                if (! Schema::hasColumn('peng_pinjamans', 'tanggal_pencairan')) {
                    $table->date('tanggal_pencairan')->nullable()->after('account_kas_id');
                }
                if (! Schema::hasColumn('peng_pinjamans', 'is_disbursed')) {
                    $table->boolean('is_disbursed')->default(false)->after('tanggal_pencairan');
                }
                if (! Schema::hasColumn('peng_pinjamans', 'post_to_ledger')) {
                    $table->boolean('post_to_ledger')->default(false)->after('is_disbursed');
                }
            });
        }

        // 4. Tambah kolom pelunasan manual & posting GL pada pemb_pinjamans
        if (Schema::hasTable('pemb_pinjamans')) {
            Schema::table('pemb_pinjamans', function (Blueprint $table) {
                if (! Schema::hasColumn('pemb_pinjamans', 'account_kas_id')) {
                    $table->unsignedBigInteger('account_kas_id')->nullable()->after('keterangan');
                }
                if (! Schema::hasColumn('pemb_pinjamans', 'post_to_ledger')) {
                    $table->boolean('post_to_ledger')->default(false)->after('account_kas_id');
                }
            });
        }

        // 5. Tambah pemetaan COA Finance & BPJS Perusahaan pada hr_payroll_settings
        if (Schema::hasTable('hr_payroll_settings')) {
            Schema::table('hr_payroll_settings', function (Blueprint $table) {
                if (! Schema::hasColumn('hr_payroll_settings', 'persen_bpjs_tk_perusahaan')) {
                    $table->decimal('persen_bpjs_tk_perusahaan', 5, 2)->default(3.70)->after('persen_bpjs_ketenagakerjaan');
                }
                if (! Schema::hasColumn('hr_payroll_settings', 'persen_bpjs_kes_perusahaan')) {
                    $table->decimal('persen_bpjs_kes_perusahaan', 5, 2)->default(4.00)->after('persen_bpjs_tk_perusahaan');
                }
                if (! Schema::hasColumn('hr_payroll_settings', 'default_account_kas_id')) {
                    $table->unsignedBigInteger('default_account_kas_id')->nullable();
                }
                if (! Schema::hasColumn('hr_payroll_settings', 'coa_beban_gaji_umum_id')) {
                    $table->unsignedBigInteger('coa_beban_gaji_umum_id')->nullable();
                }
                if (! Schema::hasColumn('hr_payroll_settings', 'coa_beban_gaji_produksi_id')) {
                    $table->unsignedBigInteger('coa_beban_gaji_produksi_id')->nullable();
                }
                if (! Schema::hasColumn('hr_payroll_settings', 'coa_beban_gaji_sales_id')) {
                    $table->unsignedBigInteger('coa_beban_gaji_sales_id')->nullable();
                }
                if (! Schema::hasColumn('hr_payroll_settings', 'coa_beban_gaji_admin_id')) {
                    $table->unsignedBigInteger('coa_beban_gaji_admin_id')->nullable();
                }
                if (! Schema::hasColumn('hr_payroll_settings', 'coa_beban_bpjs_id')) {
                    $table->unsignedBigInteger('coa_beban_bpjs_id')->nullable();
                }
                if (! Schema::hasColumn('hr_payroll_settings', 'coa_piutang_karyawan_id')) {
                    $table->unsignedBigInteger('coa_piutang_karyawan_id')->nullable();
                }
                if (! Schema::hasColumn('hr_payroll_settings', 'coa_hutang_gaji_id')) {
                    $table->unsignedBigInteger('coa_hutang_gaji_id')->nullable();
                }
                if (! Schema::hasColumn('hr_payroll_settings', 'coa_hutang_bpjs_id')) {
                    $table->unsignedBigInteger('coa_hutang_bpjs_id')->nullable();
                }
                if (! Schema::hasColumn('hr_payroll_settings', 'coa_hutang_pph21_id')) {
                    $table->unsignedBigInteger('coa_hutang_pph21_id')->nullable();
                }
                if (! Schema::hasColumn('hr_payroll_settings', 'coa_hutang_karyawan_lain_id')) {
                    $table->unsignedBigInteger('coa_hutang_karyawan_lain_id')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('gajis')) {
            Schema::table('gajis', function (Blueprint $table) {
                foreach (['bpjs_perusahaan', 'coa_beban_id', 'npb_request_id'] as $col) {
                    if (Schema::hasColumn('gajis', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('payroll_borongans')) {
            Schema::table('payroll_borongans', function (Blueprint $table) {
                foreach (['coa_beban_id', 'npb_request_id'] as $col) {
                    if (Schema::hasColumn('payroll_borongans', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('peng_pinjamans')) {
            Schema::table('peng_pinjamans', function (Blueprint $table) {
                foreach (['account_kas_id', 'tanggal_pencairan', 'is_disbursed', 'post_to_ledger'] as $col) {
                    if (Schema::hasColumn('peng_pinjamans', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('pemb_pinjamans')) {
            Schema::table('pemb_pinjamans', function (Blueprint $table) {
                foreach (['account_kas_id', 'post_to_ledger'] as $col) {
                    if (Schema::hasColumn('pemb_pinjamans', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('hr_payroll_settings')) {
            Schema::table('hr_payroll_settings', function (Blueprint $table) {
                $cols = [
                    'persen_bpjs_tk_perusahaan', 'persen_bpjs_kes_perusahaan',
                    'default_account_kas_id', 'coa_beban_gaji_umum_id', 'coa_beban_gaji_produksi_id',
                    'coa_beban_gaji_sales_id', 'coa_beban_gaji_admin_id', 'coa_beban_bpjs_id',
                    'coa_piutang_karyawan_id', 'coa_hutang_gaji_id', 'coa_hutang_bpjs_id',
                    'coa_hutang_pph21_id', 'coa_hutang_karyawan_lain_id',
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('hr_payroll_settings', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
