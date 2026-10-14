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
        if (!Schema::hasTable('bonus_karyawans')) {
            Schema::create('bonus_karyawans', function (Blueprint $table) {
                $table->id();
                $table->string('kode_gudang', 50)->nullable()->default('UGRMS');
                $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
                $table->date('tanggal_bonus');
                $table->string('periode', 20)->nullable();
                $table->decimal('nilai_bonus', 18, 2)->default(0.00);
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pot_tunjangans')) {
            Schema::create('pot_tunjangans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
                $table->string('periode_payroll', 30);
                $table->decimal('potongan_jabatan', 18, 2)->default(0.00);
                $table->decimal('potongan_kompetensi', 18, 2)->default(0.00);
                $table->decimal('potongan_intensif', 18, 2)->default(0.00);
                $table->decimal('total_potongan', 18, 2)->default(0.00);
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('hari_liburs')) {
            Schema::create('hari_liburs', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal_libur');
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gajis')) {
            Schema::create('gajis', function (Blueprint $table) {
                $table->id();
                $table->string('kode_slip', 50)->unique();
                $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
                $table->string('periode', 20); // e.g. 2026-10
                $table->date('tanggal_pembayaran');
                $table->string('metode_pembayaran', 50)->default('Transfer Bank');
                $table->foreignId('account_kas_id')->nullable()->constrained('master_coas')->onDelete('set null');
                
                // Pendapatan
                $table->decimal('gaji_pokok', 18, 2)->default(0.00);
                $table->decimal('tunjangan_jabatan', 18, 2)->default(0.00);
                $table->decimal('tunjangan_kompetensi', 18, 2)->default(0.00);
                $table->decimal('tunjangan_intensif', 18, 2)->default(0.00);
                $table->decimal('total_lembur', 18, 2)->default(0.00);
                $table->decimal('total_bonus', 18, 2)->default(0.00);
                $table->decimal('total_pendapatan', 18, 2)->default(0.00);

                // Potongan
                $table->decimal('potongan_tunjangan', 18, 2)->default(0.00);
                $table->decimal('potongan_kehadiran', 18, 2)->default(0.00);
                $table->decimal('potongan_pinjaman', 18, 2)->default(0.00);
                $table->decimal('potongan_bpjs', 18, 2)->default(0.00);
                $table->decimal('total_potongan', 18, 2)->default(0.00);

                // Take Home Pay
                $table->decimal('gaji_bersih', 18, 2)->default(0.00);

                // Status
                $table->string('status', 30)->default('Draft'); // Draft, Approved, Paid
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gajis');
        Schema::dropIfExists('hari_liburs');
        Schema::dropIfExists('pot_tunjangans');
        Schema::dropIfExists('bonus_karyawans');
    }
};
