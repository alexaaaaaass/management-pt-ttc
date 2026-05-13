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
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 255);
            $table->string('nip', 255); // Di gambar tidak terlihat eksplisit unique, tapi biasanya nip unik
            
            // Tempat & Tanggal Lahir
            $table->string('tempat_lahir', 25)->nullable();
            $table->date('tanggal_lahir')->nullable();

            // Relasi Departemen
            $table->foreignId('departemen_id')
                  ->constrained('master_departemens')
                  ->cascadeOnDelete();

            $table->string('jabatan', 255);
            $table->string('kantor', 255)->nullable();
            
            // Tanggal Terkait Kontrak
            $table->date('tanggal_masuk_kerja')->nullable();
            $table->date('tangal_akhir_kontrak')->nullable(); // Typo disesuaikan dengan gambar: 'tangal'
            
            $table->string('no_telp', 255)->nullable();
            $table->string('nik', 20);
            $table->string('no_ktp', 20);
            
            $table->enum('status_pegawai', ['Aktif', 'Nonaktif'])->nullable()->default('Aktif');
            $table->string('jenis_kelamin', 20);
            $table->string('agama', 20);

            // Penggajian & Tunjangan
            $table->integer('gaji_pokok');
            $table->string('tipe_gaji', 50);
            $table->integer('tunjangan_kompetensi');
            $table->integer('tunjangan_jabatan');
            $table->integer('tunjangan_intensif');

            // Data NPWP
            $table->string('nama_npwp', 50);
            $table->string('nomor_npwp', 30);
            $table->string('alamat_npwp', 100);
            $table->date('tanggal_npwp');
            $table->string('PTKP', 50);

            // Perbankan
            $table->string('nama_bank', 20);
            $table->string('rekening_atas_nama', 30);
            $table->string('no_rekening', 20);

            // BPJS
            $table->string('nama_bpjs', 20);
            $table->string('bpjs_kesehatan', 50);
            $table->string('bpjs_ketenagakerjaan', 50);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};