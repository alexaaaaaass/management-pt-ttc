<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Master Shift Kerja (Multi-Shift Pabrik & Kantor)
        if (! Schema::hasTable('hr_shifts')) {
            Schema::create('hr_shifts', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 30)->unique();
                $table->string('nama', 100);
                $table->string('jam_masuk', 5)->default('08:00');
                $table->string('jam_pulang', 5)->default('17:00');
                $table->unsignedSmallInteger('toleransi_telat_menit')->default(10);
                $table->boolean('is_overnight')->default(false);
                $table->string('keterangan', 255)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Seed 4 shift standar PT TTC bila tabel masih kosong
        if (DB::table('hr_shifts')->count() === 0) {
            $now = now();
            DB::table('hr_shifts')->insert([
                [
                    'kode'                  => 'NON-SHIFT',
                    'nama'                  => 'Non-Shift (Kantor / Reguler)',
                    'jam_masuk'             => '08:00',
                    'jam_pulang'            => '17:00',
                    'toleransi_telat_menit' => 10,
                    'is_overnight'          => false,
                    'keterangan'            => 'Jam kerja reguler staff kantor, HRD, Finance & PPIC',
                    'is_active'             => true,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ],
                [
                    'kode'                  => 'SHIFT-1',
                    'nama'                  => 'Shift 1 (Pagi)',
                    'jam_masuk'             => '07:00',
                    'jam_pulang'            => '15:00',
                    'toleransi_telat_menit' => 10,
                    'is_overnight'          => false,
                    'keterangan'            => 'Shift pagi operator mesin & produksi',
                    'is_active'             => true,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ],
                [
                    'kode'                  => 'SHIFT-2',
                    'nama'                  => 'Shift 2 (Sore)',
                    'jam_masuk'             => '15:00',
                    'jam_pulang'            => '23:00',
                    'toleransi_telat_menit' => 10,
                    'is_overnight'          => false,
                    'keterangan'            => 'Shift sore operator mesin & produksi',
                    'is_active'             => true,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ],
                [
                    'kode'                  => 'SHIFT-3',
                    'nama'                  => 'Shift 3 (Malam)',
                    'jam_masuk'             => '23:00',
                    'jam_pulang'            => '07:00',
                    'toleransi_telat_menit' => 10,
                    'is_overnight'          => true,
                    'keterangan'            => 'Shift malam lintas tengah malam (23:00 - 07:00)',
                    'is_active'             => true,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ],
            ]);
        }

        // 2. Relasi shift_id pada karyawans
        if (Schema::hasTable('karyawans') && ! Schema::hasColumn('karyawans', 'shift_id')) {
            Schema::table('karyawans', function (Blueprint $table) {
                $table->unsignedBigInteger('shift_id')->nullable()->after('departemen_id');
            });
            $defaultShiftId = DB::table('hr_shifts')->where('kode', 'NON-SHIFT')->value('id');
            if ($defaultShiftId) {
                DB::table('karyawans')->whereNull('shift_id')->update(['shift_id' => $defaultShiftId]);
            }
        }

        // 3. Arsip Dokumen Karyawan (KTP, KK, PKWT/PKWTT, Ijazah, BPJS, NPWP)
        if (! Schema::hasTable('karyawan_dokumens')) {
            Schema::create('karyawan_dokumens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('karyawan_id');
                $table->string('jenis_dokumen', 40);
                $table->string('nomor_dokumen', 100)->nullable();
                $table->date('tanggal_terbit')->nullable();
                $table->date('tanggal_berlaku')->nullable();
                $table->string('file_path', 255);
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->index('karyawan_id');
            });
        }

        // 4. Catatan Kedisiplinan Karyawan (Teguran, SP1, SP2, SP3, Scorsing)
        if (! Schema::hasTable('karyawan_disiplins')) {
            Schema::create('karyawan_disiplins', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('karyawan_id');
                $table->string('nomor_surat', 60)->nullable();
                $table->string('jenis', 30); // TEGURAN, SP1, SP2, SP3, SCORSING
                $table->date('tanggal_surat');
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_selesai')->nullable();
                $table->unsignedSmallInteger('jumlah_hari_scorsing')->default(0);
                $table->text('pelanggaran');
                $table->text('tindakan')->nullable();
                $table->string('lampiran', 255)->nullable();
                $table->string('status', 20)->default('Aktif'); // Aktif, Selesai, Dicabut
                $table->string('diterbitkan_oleh', 100)->nullable();
                $table->timestamps();

                $table->index('karyawan_id');
            });
        }

        // 5. Upgrade hr_payroll_settings (Cut-off 26-25, Uang Makan Lembur > 2 Jam, Metode Potongan Telat)
        if (Schema::hasTable('hr_payroll_settings')) {
            Schema::table('hr_payroll_settings', function (Blueprint $table) {
                if (! Schema::hasColumn('hr_payroll_settings', 'pakai_cutoff_bulanan')) {
                    $table->boolean('pakai_cutoff_bulanan')->default(true)->after('kuota_cuti_tahunan');
                }
                if (! Schema::hasColumn('hr_payroll_settings', 'cutoff_tanggal_mulai')) {
                    $table->unsignedTinyInteger('cutoff_tanggal_mulai')->default(26)->after('pakai_cutoff_bulanan');
                }
                if (! Schema::hasColumn('hr_payroll_settings', 'cutoff_tanggal_selesai')) {
                    $table->unsignedTinyInteger('cutoff_tanggal_selesai')->default(25)->after('cutoff_tanggal_mulai');
                }
                if (! Schema::hasColumn('hr_payroll_settings', 'uang_makan_lembur_nominal')) {
                    $table->decimal('uang_makan_lembur_nominal', 12, 2)->default(15000)->after('pengali_lembur_hari_libur');
                }
                if (! Schema::hasColumn('hr_payroll_settings', 'min_jam_uang_makan_lembur')) {
                    $table->decimal('min_jam_uang_makan_lembur', 5, 2)->default(2.00)->after('uang_makan_lembur_nominal');
                }
                if (! Schema::hasColumn('hr_payroll_settings', 'metode_potongan_telat')) {
                    $table->string('metode_potongan_telat', 20)->default('FLAT')->after('potongan_telat_per_kali');
                }
                if (! Schema::hasColumn('hr_payroll_settings', 'potongan_sakit_tanpa_surat')) {
                    $table->boolean('potongan_sakit_tanpa_surat')->default(false)->after('potongan_alpha_per_hari');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('karyawans') && Schema::hasColumn('karyawans', 'shift_id')) {
            Schema::table('karyawans', fn (Blueprint $t) => $t->dropColumn('shift_id'));
        }
        Schema::dropIfExists('karyawan_dokumens');
        Schema::dropIfExists('karyawan_disiplins');
        Schema::dropIfExists('hr_shifts');

        if (Schema::hasTable('hr_payroll_settings')) {
            Schema::table('hr_payroll_settings', function (Blueprint $table) {
                foreach ([
                    'pakai_cutoff_bulanan', 'cutoff_tanggal_mulai', 'cutoff_tanggal_selesai',
                    'uang_makan_lembur_nominal', 'min_jam_uang_makan_lembur',
                    'metode_potongan_telat', 'potongan_sakit_tanpa_surat',
                ] as $col) {
                    if (Schema::hasColumn('hr_payroll_settings', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
