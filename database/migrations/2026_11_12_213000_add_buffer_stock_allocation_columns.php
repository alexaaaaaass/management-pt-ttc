<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('finish_good_stocks') && ! Schema::hasColumn('finish_good_stocks', 'reserved')) {
            Schema::table('finish_good_stocks', function (Blueprint $table) {
                $table->decimal('reserved', 18, 2)->default(0.00)->after('on_hand');
            });
        }

        if (Schema::hasTable('sales_orders')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                if (! Schema::hasColumn('sales_orders', 'fulfillment_type')) {
                    $table->string('fulfillment_type', 30)->default('PRODUCTION')->after('order_type');
                }
                if (! Schema::hasColumn('sales_orders', 'qty_from_stock')) {
                    $table->decimal('qty_from_stock', 18, 2)->default(0.00)->after('qty');
                }
                if (! Schema::hasColumn('sales_orders', 'qty_to_produce')) {
                    $table->decimal('qty_to_produce', 18, 2)->default(0.00)->after('qty_from_stock');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('finish_good_stocks') && Schema::hasColumn('finish_good_stocks', 'reserved')) {
            Schema::table('finish_good_stocks', function (Blueprint $table) {
                $table->dropColumn('reserved');
            });
        }

        if (Schema::hasTable('sales_orders')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $cols = array_filter(['fulfillment_type', 'qty_from_stock', 'qty_to_produce'], fn ($c) => Schema::hasColumn('sales_orders', $c));
                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
