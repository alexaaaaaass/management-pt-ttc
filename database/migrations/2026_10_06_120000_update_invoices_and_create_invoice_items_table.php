<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah field tambahan pada tabel invoices (hanya yang belum ada)
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'order_id')) {
                $table->string('order_id')->nullable()->after('kode_invoice');
            }
            if (!Schema::hasColumn('invoices', 'dibuat_oleh')) {
                $table->string('dibuat_oleh')->nullable()->default('PT TEKNOGRAFI TRI CAWANASKA')->after('tanggal_jatuh_tempo');
            }
            if (!Schema::hasColumn('invoices', 'kepada')) {
                $table->string('kepada')->nullable()->after('dibuat_oleh');
            }
            if (!Schema::hasColumn('invoices', 'terms_conditions')) {
                $table->text('terms_conditions')->nullable()->after('catatan');
            }
            if (!Schema::hasColumn('invoices', 'bank_accounts')) {
                $table->text('bank_accounts')->nullable()->after('terms_conditions');
            }
            if (!Schema::hasColumn('invoices', 'terbilang_override')) {
                $table->string('terbilang_override')->nullable()->after('bank_accounts');
            }
        });

        // 2. Buat tabel invoice_items untuk rincian item invoice
        if (!Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
                $table->string('item')->nullable();
                $table->string('jenis')->nullable();
                $table->string('komponen')->nullable();
                $table->string('kertas')->nullable();
                $table->string('finishing')->nullable();
                $table->double('qty')->default(0);
                $table->double('harga')->default(0);
                $table->double('total')->default(0);
                $table->string('keterangan')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');

        Schema::table('invoices', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['order_id', 'dibuat_oleh', 'kepada', 'terms_conditions', 'bank_accounts', 'terbilang_override'] as $col) {
                if (Schema::hasColumn('invoices', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
