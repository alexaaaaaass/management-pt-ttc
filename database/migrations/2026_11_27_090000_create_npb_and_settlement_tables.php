<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('npb_requests')) {
            Schema::create('npb_requests', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_pengajuan', 60)->unique();
                $table->date('tanggal_pengajuan');
                $table->string('departemen_kode', 20)->default('KEU');
                $table->string('perihal', 255);
                $table->string('periode_utama', 50);
                $table->string('periode_proyeksi', 50)->nullable();

                $table->decimal('total_kebutuhan_utama', 18, 2)->default(0);
                $table->decimal('total_kebutuhan_proyeksi', 18, 2)->default(0);
                $table->decimal('total_kebutuhan_gabungan', 18, 2)->default(0);
                $table->decimal('total_saldo_sebelumnya', 18, 2)->default(0);
                $table->decimal('total_dana_tersedia', 18, 2)->default(0);
                $table->decimal('kekurangan_pendanaan', 18, 2)->default(0);

                $table->text('catatan')->nullable();
                $table->string('status', 30)->default('DRAFT');

                // Otorisasi 4 pihak sesuai format NPB PT TTC
                $table->string('disusun_oleh', 120)->nullable();
                $table->string('mengetahui_nama', 120)->nullable();
                $table->timestamp('mengetahui_at')->nullable();
                $table->string('diajukan_kepada', 120)->nullable();
                $table->string('disetujui_oleh', 120)->nullable();
                $table->timestamp('disetujui_at')->nullable();
                $table->text('catatan_approval')->nullable();

                // Pencairan Dana (Opsional / Bertahap)
                $table->date('tanggal_pencairan')->nullable();
                $table->decimal('nominal_dicairkan', 18, 2)->default(0);
                $table->string('sumber_dana_pencairan', 40)->nullable();
                $table->unsignedBigInteger('coa_tujuan_pencairan_id')->nullable();
                $table->boolean('is_pencairan_posted')->default(false);

                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['tanggal_pengajuan', 'status']);
            });
        }

        if (! Schema::hasTable('npb_fund_positions')) {
            Schema::create('npb_fund_positions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('npb_request_id')
                    ->constrained('npb_requests')
                    ->cascadeOnDelete();
                $table->unsignedInteger('urutan')->default(1);
                $table->unsignedBigInteger('coa_id')->nullable();
                $table->string('nama_rekening', 120);
                $table->decimal('saldo', 18, 2)->default(0);
                $table->string('keterangan', 255)->nullable();
                $table->timestamps();

                $table->index('npb_request_id');
            });
        }

        if (! Schema::hasTable('npb_items')) {
            Schema::create('npb_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('npb_request_id')
                    ->constrained('npb_requests')
                    ->cascadeOnDelete();
                $table->unsignedInteger('urutan')->default(1);
                $table->string('kategori', 50)->default('A_KEWAJIBAN_TETAP');
                $table->string('uraian', 255);
                $table->string('karakter', 30)->default('Bulanan');
                $table->unsignedBigInteger('coa_id')->nullable();
                $table->decimal('nominal_utama', 18, 2)->default(0);
                $table->decimal('nominal_proyeksi', 18, 2)->default(0);
                $table->string('keterangan', 255)->nullable();
                $table->timestamps();

                $table->index(['npb_request_id', 'kategori']);
            });
        }

        if (! Schema::hasTable('npb_settlements')) {
            Schema::create('npb_settlements', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_penyelesaian', 60)->unique();
                $table->foreignId('npb_request_id')
                    ->constrained('npb_requests')
                    ->cascadeOnDelete();
                $table->date('tanggal_penyelesaian');
                $table->string('periode_realisasi', 20)->default('UTAMA');
                $table->string('label_periode', 60)->nullable();

                $table->decimal('total_anggaran', 18, 2)->default(0);
                $table->decimal('total_dana_dicairkan', 18, 2)->default(0);
                $table->decimal('total_realisasi', 18, 2)->default(0);
                $table->decimal('selisih_anggaran', 18, 2)->default(0);
                $table->decimal('sisa_dana', 18, 2)->default(0);

                $table->string('tindak_lanjut_sisa', 40)->default('CARRY_OVER');
                $table->string('posting_mode', 30)->default('MANUAL_LINK_ONLY');
                $table->boolean('is_posted_to_ledger')->default(false);
                $table->string('status', 30)->default('DRAFT');
                $table->text('catatan')->nullable();

                $table->string('disusun_oleh', 120)->nullable();
                $table->string('diperiksa_oleh', 120)->nullable();
                $table->string('disetujui_oleh', 120)->nullable();
                $table->timestamp('disetujui_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['npb_request_id', 'status']);
            });
        }

        if (! Schema::hasTable('npb_settlement_items')) {
            Schema::create('npb_settlement_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('npb_settlement_id')
                    ->constrained('npb_settlements')
                    ->cascadeOnDelete();
                $table->unsignedBigInteger('npb_item_id')->nullable();
                $table->unsignedInteger('urutan')->default(1);
                $table->string('kategori', 50)->default('A_KEWAJIBAN_TETAP');
                $table->string('uraian', 255);
                $table->string('karakter', 30)->default('Bulanan');

                $table->decimal('nominal_anggaran', 18, 2)->default(0);
                $table->decimal('nominal_realisasi', 18, 2)->default(0);
                $table->decimal('selisih', 18, 2)->default(0);

                $table->date('tanggal_realisasi')->nullable();
                $table->string('metode_pembayaran', 30)->default('BANK_TTC');
                $table->unsignedBigInteger('coa_beban_id')->nullable();
                $table->unsignedBigInteger('coa_sumber_id')->nullable();
                $table->string('no_referensi_bukti', 120)->nullable();
                $table->string('lampiran_bukti', 255)->nullable();
                $table->string('status_item', 30)->default('TEREALISASI');
                $table->string('keterangan', 255)->nullable();
                $table->timestamps();

                $table->index(['npb_settlement_id', 'kategori']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('npb_settlement_items');
        Schema::dropIfExists('npb_settlements');
        Schema::dropIfExists('npb_items');
        Schema::dropIfExists('npb_fund_positions');
        Schema::dropIfExists('npb_requests');
    }
};
