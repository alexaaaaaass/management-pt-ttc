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
        Schema::create('imr_printing', function (Blueprint $table) {
                $table->id();

        $table->string('no_imr')->nullable();

        $table->foreignId('spk_id')
            ->constrained('master_spks');

        $table->foreignId('departemen_id')
            ->constrained('master_departemens');

        $table->date('tanggal_request');

        $table->enum('status',[

            'Pending',

            'Approved',

            'Partial',

            'Rejected',

            'Closed',

        ])->default('Pending');

        $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};