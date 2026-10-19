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
        Schema::table('finish_good_item_materials', function (Blueprint $table) {
            $table->double('qty', 15, 4)->nullable()->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finish_good_item_materials', function (Blueprint $table) {
            $table->double('qty', 15, 4)->nullable(false)->change();
        });
    }
};
