<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('imr_finishes')) {
            Schema::create('imr_finishes', function (Blueprint $table) {
                $table->id();
                $table->string('no_imr')->nullable()->index();
                $table->foreignId('spk_id')
                    ->constrained('master_spk')
                    ->cascadeOnDelete();
                $table->foreignId('departemen_id')
                    ->nullable()
                    ->constrained('master_departemens')
                    ->nullOnDelete();
                $table->date('tanggal_request');
                $table->enum('status', [
                    'Pending',
                    'Approved',
                    'Partial',
                    'Issued',
                    'Partial Issued',
                    'Rejected',
                    'Closed',
                ])->default('Pending');
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('imr_finish_items')) {
            Schema::create('imr_finish_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('imr_finish_id')
                    ->constrained('imr_finishes')
                    ->cascadeOnDelete();
                $table->foreignId('item_id')
                    ->constrained('master_items');
                $table->decimal('total_pesanan', 15, 2)->default(0);
                $table->decimal('qty_request', 15, 2)->default(0);
                $table->decimal('qty_approved', 15, 2)->default(0);
                $table->decimal('qty_issued', 15, 2)->default(0);
                $table->decimal('qty_returned', 15, 2)->default(0);
                $table->string('satuan')->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }

        // Pastikan departemen Finishing tersedia di master_departemens
        if (Schema::hasTable('master_departemens')) {
            $exists = DB::table('master_departemens')->where('kode', 'FNSH')->exists();
            if (!$exists) {
                DB::table('master_departemens')->insert([
                    'kode' => 'FNSH',
                    'nama_departemen' => 'Finishing',
                    'deskripsi' => 'Departemen Finishing',
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imr_finish_items');
        Schema::dropIfExists('imr_finishes');
    }
};
