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
    Schema::table('master_coas', function (Blueprint $table) {
        $table->string('parent_code')->nullable();
        $table->unsignedInteger('level')->nullable();
        $table->string('account_type')->nullable();
        $table->string('account_class')->nullable();
        $table->string('financial_statement')->nullable();
        $table->string('cash_flow_type')->nullable();
        $table->string('posting')->nullable();
        $table->string('normal_balance')->nullable();
    });
}

public function down(): void
{
    Schema::table('master_coas', function (Blueprint $table) {
        $table->dropColumn([
            'parent_code',
            'level',
            'account_type',
            'account_class',
            'financial_statement',
            'cash_flow_type',
            'posting',
            'normal_balance',
        ]);
    });
}
};   