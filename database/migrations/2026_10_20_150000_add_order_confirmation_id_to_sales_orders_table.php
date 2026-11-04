<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('sales_orders', 'order_confirmation_id')) {
                $table->foreignId('order_confirmation_id')
                    ->nullable()
                    ->after('customer_id')
                    ->constrained('order_confirmations')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            if (Schema::hasColumn('sales_orders', 'order_confirmation_id')) {
                $table->dropConstrainedForeignId('order_confirmation_id');
            }
        });
    }
};
