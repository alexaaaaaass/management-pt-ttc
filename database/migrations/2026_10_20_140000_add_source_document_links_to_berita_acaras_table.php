<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('berita_acaras', function (Blueprint $table) {
            if (! Schema::hasColumn('berita_acaras', 'surat_jalan_id')) {
                $table->foreignId('surat_jalan_id')
                    ->nullable()
                    ->after('customer_id')
                    ->constrained('surat_jalans')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('berita_acaras', 'invoice_id')) {
                $table->foreignId('invoice_id')
                    ->nullable()
                    ->after('surat_jalan_id')
                    ->constrained('invoices')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('berita_acaras', function (Blueprint $table) {
            if (Schema::hasColumn('berita_acaras', 'invoice_id')) {
                $table->dropConstrainedForeignId('invoice_id');
            }

            if (Schema::hasColumn('berita_acaras', 'surat_jalan_id')) {
                $table->dropConstrainedForeignId('surat_jalan_id');
            }
        });
    }
};