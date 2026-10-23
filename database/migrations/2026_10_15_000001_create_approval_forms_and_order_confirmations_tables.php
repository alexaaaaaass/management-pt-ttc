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
        // 1. APPROVAL FORMS (Spesifikasi Teknis Produksi & Kesiapan Mesin/Desain)
        Schema::create('approval_forms', function (Blueprint $table) {
            $table->id();
            $table->string('order_id', 100)->nullable()->index(); // OR-26-IV-C005-003
            $table->string('form_no', 100)->unique(); // AF-26-IV-C005-003
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal');
            $table->string('lead_time', 100)->nullable();
            $table->text('catatan_produksi')->nullable();
            $table->string('status', 50)->default('draft'); // draft, reviewed_marketing, approved_produksi, converted_to_oc, cancelled
            $table->string('disetujui_marketing', 150)->nullable();
            $table->string('disetujui_produksi', 150)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. APPROVAL FORM ITEMS
        Schema::create('approval_form_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_form_id')->constrained('approval_forms')->cascadeOnDelete();
            $table->foreignId('finish_good_item_id')->nullable()->constrained('finish_good_items')->nullOnDelete();
            $table->string('item_name', 255);
            $table->string('jenis', 100)->nullable(); // SKT, SKM
            $table->string('komponen', 100)->nullable(); // Etiket, Inner, Sliding Luar, Sliding Dalam
            $table->string('isi', 50)->nullable(); // 20, 16, 12 batang
            $table->string('kertas', 255)->nullable(); // Metalize, Ivory, Duplex
            $table->string('warna', 100)->nullable(); // 5, 4, Full Color
            $table->text('finishing')->nullable(); // Spot UV, Emboss, Lem, Cutting
            $table->decimal('qty', 15, 2)->default(0);
            $table->string('satuan', 50)->default('Pcs');
            
            // Checklist Teknis Internal (Sesuai kolom kecil di tabel Approval Form)
            $table->boolean('cek_desain')->default(false);
            $table->boolean('cek_plate')->default(false);
            $table->boolean('cek_produksi')->default(false);
            $table->boolean('cek_qc')->default(false);
            $table->timestamps();
        });

        // 3. ORDER CONFIRMATIONS (Aspek Komersial, Harga, & Persetujuan Customer)
        Schema::create('order_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_form_id')->nullable()->constrained('approval_forms')->nullOnDelete();
            $table->string('order_id', 100)->nullable()->index(); // OR-26-VII-C006-003
            $table->string('doc_no', 100)->unique(); // SC-26-VII-C006-003
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal');
            $table->string('lead_time', 100)->default('21 Hari Kerja')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->text('terms_conditions')->nullable();
            $table->text('catatan')->nullable();
            $table->string('status', 50)->default('draft'); // draft, sent_to_customer, deal, rejected, converted_to_so
            $table->string('disetujui_customer', 150)->nullable();
            $table->string('diketahui_ttc', 150)->default('PT. Teknografi Tri Cawanaska')->nullable();
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 4. ORDER CONFIRMATION ITEMS
        Schema::create('order_confirmation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_confirmation_id')->constrained('order_confirmations')->cascadeOnDelete();
            $table->foreignId('approval_form_item_id')->nullable()->constrained('approval_form_items')->nullOnDelete();
            $table->foreignId('finish_good_item_id')->nullable()->constrained('finish_good_items')->nullOnDelete();
            $table->string('item_name', 255);
            $table->string('jenis', 100)->nullable();
            $table->string('komponen', 100)->nullable();
            $table->string('isi', 50)->nullable();
            $table->string('kertas', 255)->nullable();
            $table->string('warna', 100)->nullable();
            $table->text('finishing')->nullable();
            $table->decimal('qty', 15, 2)->default(0);
            $table->decimal('harga', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('satuan', 50)->default('Pcs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_confirmation_items');
        Schema::dropIfExists('order_confirmations');
        Schema::dropIfExists('approval_form_items');
        Schema::dropIfExists('approval_forms');
    }
};
