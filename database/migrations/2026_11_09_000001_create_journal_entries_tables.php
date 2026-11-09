<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('journal_entries')) {
            Schema::create('journal_entries', function (Blueprint $table) {
                $table->id();
                $table->string('kode_jurnal', 50)->unique();
                $table->date('tanggal');
                $table->string('periode', 50)->nullable();
                $table->string('gudang', 50)->nullable()->default('TTC');
                $table->string('jenis_jurnal', 50)->default('MEMORIAL');
                $table->string('no_referensi', 100)->nullable();
                $table->unsignedBigInteger('karyawan_id')->nullable();
                $table->text('keterangan')->nullable();
                $table->decimal('total_debit', 18, 2)->default(0);
                $table->decimal('total_kredit', 18, 2)->default(0);
                $table->string('status', 30)->default('POSTED');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['tanggal', 'status']);
            });
        }

        if (! Schema::hasTable('journal_entry_lines')) {
            Schema::create('journal_entry_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('journal_entry_id')
                    ->constrained('journal_entries')
                    ->cascadeOnDelete();
                $table->foreignId('coa_id')
                    ->constrained('master_coas')
                    ->restrictOnDelete();
                $table->string('keterangan')->nullable();
                $table->decimal('debit', 18, 2)->default(0);
                $table->decimal('kredit', 18, 2)->default(0);
                $table->timestamps();

                $table->index(['journal_entry_id', 'coa_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
    }
};

