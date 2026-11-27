<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Perbaiki skema tabel karyawans (jadikan kolom opsional NULLABLE, perlebar varchar, upgrade decimal, tambah field HRD)
        if (Schema::hasTable('karyawans')) {
            DB::statement("ALTER TABLE `karyawans`
                MODIFY `tempat_lahir` VARCHAR(100) NULL,
                MODIFY `nik` VARCHAR(30) NULL,
                MODIFY `no_ktp` VARCHAR(30) NULL,
                MODIFY `jenis_kelamin` VARCHAR(20) NULL,
                MODIFY `agama` VARCHAR(30) NULL,
                MODIFY `gaji_pokok` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
                MODIFY `tipe_gaji` VARCHAR(50) NOT NULL DEFAULT 'Bulanan',
                MODIFY `tunjangan_kompetensi` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
                MODIFY `tunjangan_jabatan` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
                MODIFY `tunjangan_intensif` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
                MODIFY `nama_npwp` VARCHAR(100) NULL,
                MODIFY `nomor_npwp` VARCHAR(50) NULL,
                MODIFY `alamat_npwp` VARCHAR(255) NULL,
                MODIFY `tanggal_npwp` DATE NULL,
                MODIFY `PTKP` VARCHAR(50) NULL,
                MODIFY `nama_bank` VARCHAR(50) NULL,
                MODIFY `rekening_atas_nama` VARCHAR(100) NULL,
                MODIFY `no_rekening` VARCHAR(50) NULL,
                MODIFY `nama_bpjs` VARCHAR(100) NULL,
                MODIFY `bpjs_kesehatan` VARCHAR(50) NULL,
                MODIFY `bpjs_ketenagakerjaan` VARCHAR(50) NULL
            ");

            Schema::table('karyawans', function (Blueprint $table) {
                if (! Schema::hasColumn('karyawans', 'pin_finger')) {
                    $table->string('pin_finger', 50)->nullable()->after('nip');
                }
                if (! Schema::hasColumn('karyawans', 'tunjangan_makan')) {
                    $table->decimal('tunjangan_makan', 18, 2)->default(0)->after('tunjangan_intensif');
                }
                if (! Schema::hasColumn('karyawans', 'tunjangan_hadir')) {
                    $table->decimal('tunjangan_hadir', 18, 2)->default(0)->after('tunjangan_makan');
                }
                if (! Schema::hasColumn('karyawans', 'tunjangan_lain')) {
                    $table->decimal('tunjangan_lain', 18, 2)->default(0)->after('tunjangan_hadir');
                }
                if (! Schema::hasColumn('karyawans', 'tarif_lembur_custom')) {
                    $table->decimal('tarif_lembur_custom', 18, 2)->nullable()->after('tunjangan_lain');
                }
                if (! Schema::hasColumn('karyawans', 'bpjs_kesehatan_nominal')) {
                    $table->decimal('bpjs_kesehatan_nominal', 18, 2)->default(0)->after('bpjs_ketenagakerjaan');
                }
                if (! Schema::hasColumn('karyawans', 'bpjs_ketenagakerjaan_nominal')) {
                    $table->decimal('bpjs_ketenagakerjaan_nominal', 18, 2)->default(0)->after('bpjs_kesehatan_nominal');
                }
                if (! Schema::hasColumn('karyawans', 'is_borongan')) {
                    $table->boolean('is_borongan')->default(false)->after('tipe_gaji');
                }
            });
        }

        // 2. Tambah status approval di cutis, izins, lemburs, peng_pinjamans + perluas jenis_izin
        foreach (['cutis', 'izins', 'lemburs', 'peng_pinjamans'] as $tbl) {
            if (Schema::hasTable($tbl)) {
                Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                    if (! Schema::hasColumn($tbl, 'status')) {
                        $table->string('status', 20)->default('Approved');
                    }
                    if (! Schema::hasColumn($tbl, 'approved_by')) {
                        $table->string('approved_by', 100)->nullable();
                    }
                    if (! Schema::hasColumn($tbl, 'approved_at')) {
                        $table->timestamp('approved_at')->nullable();
                    }
                    if (! Schema::hasColumn($tbl, 'catatan_approval')) {
                        $table->text('catatan_approval')->nullable();
                    }
                });
            }
        }

        if (Schema::hasTable('izins')) {
            DB::statement("ALTER TABLE `izins` MODIFY `jenis_izin` VARCHAR(40) NOT NULL");
            Schema::table('izins', function (Blueprint $table) {
                if (! Schema::hasColumn('izins', 'lampiran')) {
                    $table->string('lampiran', 255)->nullable()->after('keterangan');
                }
            });
        }

        // 3. Upgrade tabel gajis (support Harian/Freelance, rentang tgl, kehadiran, tunjangan/potongan tambahan, sinkron pinjaman & GL)
        if (Schema::hasTable('gajis')) {
            Schema::table('gajis', function (Blueprint $table) {
                if (! Schema::hasColumn('gajis', 'tipe_payroll')) {
                    $table->string('tipe_payroll', 30)->default('Bulanan')->after('periode');
                }
                if (! Schema::hasColumn('gajis', 'tanggal_mulai')) {
                    $table->date('tanggal_mulai')->nullable()->after('tipe_payroll');
                }
                if (! Schema::hasColumn('gajis', 'tanggal_selesai')) {
                    $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
                }
                if (! Schema::hasColumn('gajis', 'jumlah_hari_hadir')) {
                    $table->decimal('jumlah_hari_hadir', 6, 2)->default(0)->after('tanggal_selesai');
                }
                if (! Schema::hasColumn('gajis', 'jumlah_alpha')) {
                    $table->integer('jumlah_alpha')->default(0)->after('jumlah_hari_hadir');
                }
                if (! Schema::hasColumn('gajis', 'jumlah_terlambat')) {
                    $table->integer('jumlah_terlambat')->default(0)->after('jumlah_alpha');
                }
                if (! Schema::hasColumn('gajis', 'tunjangan_makan')) {
                    $table->decimal('tunjangan_makan', 18, 2)->default(0)->after('tunjangan_intensif');
                }
                if (! Schema::hasColumn('gajis', 'tunjangan_hadir')) {
                    $table->decimal('tunjangan_hadir', 18, 2)->default(0)->after('tunjangan_makan');
                }
                if (! Schema::hasColumn('gajis', 'tunjangan_lain')) {
                    $table->decimal('tunjangan_lain', 18, 2)->default(0)->after('tunjangan_hadir');
                }
                if (! Schema::hasColumn('gajis', 'potongan_pph21')) {
                    $table->decimal('potongan_pph21', 18, 2)->default(0)->after('potongan_bpjs');
                }
                if (! Schema::hasColumn('gajis', 'potongan_lain')) {
                    $table->decimal('potongan_lain', 18, 2)->default(0)->after('potongan_pph21');
                }
                if (! Schema::hasColumn('gajis', 'is_pinjaman_synced')) {
                    $table->boolean('is_pinjaman_synced')->default(false)->after('status');
                }
                if (! Schema::hasColumn('gajis', 'post_to_ledger')) {
                    $table->boolean('post_to_ledger')->default(true)->after('is_pinjaman_synced');
                }
            });
        }

        // 4. Link pemb_pinjamans ke gajis (jejak potong gaji otomatis)
        if (Schema::hasTable('pemb_pinjamans')) {
            Schema::table('pemb_pinjamans', function (Blueprint $table) {
                if (! Schema::hasColumn('pemb_pinjamans', 'gaji_id')) {
                    $table->foreignId('gaji_id')->nullable()->after('karyawan_id')->constrained('gajis')->nullOnDelete();
                }
            });
        }

        // 5. Tabel baru: hr_payroll_settings (Master Aturan Payroll, Shift, Lembur, BPJS, Borongan)
        if (! Schema::hasTable('hr_payroll_settings')) {
            Schema::create('hr_payroll_settings', function (Blueprint $table) {
                $table->id();
                $table->string('jam_masuk_standar', 10)->default('08:00');
                $table->string('jam_pulang_standar', 10)->default('17:00');
                $table->integer('toleransi_telat_menit')->default(10);
                $table->integer('kuota_cuti_tahunan')->default(12);
                $table->string('metode_lembur', 30)->default('PER_173'); // PER_173 | TARIF_TETAP
                $table->decimal('tarif_lembur_tetap_per_jam', 18, 2)->default(15000);
                $table->decimal('pengali_lembur_hari_kerja', 6, 2)->default(1.00);
                $table->decimal('pengali_lembur_hari_libur', 6, 2)->default(2.00);
                $table->decimal('potongan_telat_per_kali', 18, 2)->default(10000);
                $table->decimal('potongan_alpha_per_hari', 18, 2)->default(0); // 0 = otomatis 1/25 gaji pokok
                $table->decimal('persen_bpjs_kesehatan', 5, 2)->default(1.00);
                $table->decimal('persen_bpjs_ketenagakerjaan', 5, 2)->default(2.00);
                $table->boolean('auto_hitung_bpjs')->default(false);
                $table->decimal('tarif_protol_default', 18, 2)->default(0);
                $table->decimal('tarif_packing_default', 18, 2)->default(0);
                $table->decimal('tarif_lain_borongan_default', 18, 2)->default(0);
                $table->decimal('tunj_hadir_borongan_default', 18, 2)->default(0);
                $table->decimal('tunj_makan_borongan_default', 18, 2)->default(0);
                $table->timestamps();
            });

            DB::table('hr_payroll_settings')->insert([
                'jam_masuk_standar'           => '08:00',
                'jam_pulang_standar'          => '17:00',
                'toleransi_telat_menit'       => 10,
                'kuota_cuti_tahunan'          => 12,
                'metode_lembur'               => 'PER_173',
                'tarif_lembur_tetap_per_jam'  => 15000,
                'pengali_lembur_hari_kerja'   => 1.00,
                'pengali_lembur_hari_libur'   => 2.00,
                'potongan_telat_per_kali'     => 10000,
                'potongan_alpha_per_hari'     => 0,
                'persen_bpjs_kesehatan'       => 1.00,
                'persen_bpjs_ketenagakerjaan' => 2.00,
                'auto_hitung_bpjs'            => false,
                'tarif_protol_default'        => 0,
                'tarif_packing_default'       => 0,
                'tarif_lain_borongan_default' => 0,
                'tunj_hadir_borongan_default' => 0,
                'tunj_makan_borongan_default' => 0,
                'created_at'                  => now(),
                'updated_at'                  => now(),
            ]);
        }

        // 6. Tabel baru: payroll_borongans & payroll_borongan_items (Payroll Borongan / Part-Time)
        if (! Schema::hasTable('payroll_borongans')) {
            Schema::create('payroll_borongans', function (Blueprint $table) {
                $table->id();
                $table->string('kode_bukti', 60)->unique();
                $table->string('periode', 20); // YYYY-MM
                $table->date('tanggal_transaksi');
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                $table->string('kode_gudang', 50)->default('UGRMS');
                $table->string('metode_pembayaran', 50)->default('Tunai');
                $table->foreignId('account_kas_id')->nullable()->constrained('master_coas')->nullOnDelete();
                $table->decimal('total_gaji_borongan', 18, 2)->default(0);
                $table->decimal('total_tunjangan', 18, 2)->default(0);
                $table->decimal('total_potongan', 18, 2)->default(0);
                $table->decimal('grand_total', 18, 2)->default(0);
                $table->string('status', 30)->default('Draft'); // Draft | Approved | Paid
                $table->boolean('post_to_ledger')->default(false);
                $table->text('keterangan')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('payroll_borongan_items')) {
            Schema::create('payroll_borongan_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payroll_borongan_id')->constrained('payroll_borongans')->cascadeOnDelete();
                $table->integer('urutan')->default(1);
                $table->foreignId('karyawan_id')->nullable()->constrained('karyawans')->nullOnDelete();
                $table->string('nik_pekerja', 50)->nullable();
                $table->string('nama_pekerja', 150);
                $table->date('tanggal_kerja')->nullable();
                $table->string('jenis_pekerjaan', 100)->nullable();
                $table->decimal('protol_qty', 14, 2)->default(0);
                $table->decimal('protol_tarif', 18, 2)->default(0);
                $table->decimal('protol_gaji', 18, 2)->default(0);
                $table->decimal('packing_qty', 14, 2)->default(0);
                $table->decimal('packing_tarif', 18, 2)->default(0);
                $table->decimal('packing_gaji', 18, 2)->default(0);
                $table->decimal('lain_qty', 14, 2)->default(0);
                $table->decimal('lain_tarif', 18, 2)->default(0);
                $table->decimal('lain_gaji', 18, 2)->default(0);
                $table->decimal('tunj_hadir', 18, 2)->default(0);
                $table->decimal('tunj_makan', 18, 2)->default(0);
                $table->decimal('tunj_lain', 18, 2)->default(0);
                $table->decimal('potongan', 18, 2)->default(0);
                $table->decimal('total', 18, 2)->default(0);
                $table->string('keterangan', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_borongan_items');
        Schema::dropIfExists('payroll_borongans');
        Schema::dropIfExists('hr_payroll_settings');
    }
};
