<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('imr_finishes')) {
            Schema::create('imr_finishes', function (Blueprint $table) {
                $table->id();
                $table->string('no_imr')->nullable()->unique();
                $table->foreignId('spk_id')->constrained('master_spk');
                $table->foreignId('departemen_id')->nullable()->constrained('master_departemens')->nullOnDelete();
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
    }

    public function down(): void
    {
        Schema::dropIfExists('imr_finishes');
    }
};
