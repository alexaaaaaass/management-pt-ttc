<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Invoice;
use App\Observers\InvoiceObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Invoice::observe(
            InvoiceObserver::class
        );

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('sales_orders')) {
                if (! \Illuminate\Support\Facades\Schema::hasColumn('sales_orders', 'product_name')) {
                    \Illuminate\Support\Facades\Schema::table('sales_orders', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->string('product_name')->nullable()->after('kode_material');
                    });
                }
                if (! \Illuminate\Support\Facades\Schema::hasColumn('sales_orders', 'order_confirmation_id')) {
                    $ocMig = base_path('database/migrations/2026_10_20_150000_add_order_confirmation_id_to_sales_orders_table.php');
                    if (file_exists($ocMig)) {
                        (require $ocMig)->up();
                    }
                }
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('order_confirmation_items') && ! \Illuminate\Support\Facades\Schema::hasColumn('order_confirmation_items', 'hpp_per_pcs')) {
                $hppMig = base_path('database/migrations/2026_10_31_210000_add_hpp_and_paper_specs_to_order_confirmation_items_table.php');
                if (file_exists($hppMig)) {
                    (require $hppMig)->up();
                }
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('invoices') && ! \Illuminate\Support\Facades\Schema::hasColumn('invoices', 'potongan')) {
                $potMig = base_path('database/migrations/2026_10_07_000000_add_potongan_to_invoices_table.php');
                if (file_exists($potMig)) {
                    (require $potMig)->up();
                }
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('finish_good_items')) {
                \Illuminate\Support\Facades\DB::table('finish_good_items')
                    ->whereRaw("UPPER(TRIM(nama_barang)) = 'KERTAS IVORY'")
                    ->update(['nama_barang' => 'DAFI 16', 'spesifikasi_kertas' => 'IVORY']);
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('approval_form_items')) {
                \Illuminate\Support\Facades\DB::table('approval_form_items')
                    ->whereRaw("UPPER(TRIM(item_name)) = 'KERTAS IVORY'")
                    ->update(['item_name' => 'DAFI 16']);
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('order_confirmation_items')) {
                \Illuminate\Support\Facades\DB::table('order_confirmation_items')
                    ->whereRaw("UPPER(TRIM(item_name)) = 'KERTAS IVORY'")
                    ->update(['item_name' => 'DAFI 16']);
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('users') && ! \Illuminate\Support\Facades\Schema::hasTable('roles')) {
                $permMigrationPath = base_path('database/migrations/2026_10_30_230329_create_permission_tables.php');
                if (file_exists($permMigrationPath)) {
                    $permMigration = require $permMigrationPath;
                    $permMigration->up();
                }
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('roles') && \Illuminate\Support\Facades\Schema::hasTable('model_has_roles')) {
                $superRole = \Spatie\Permission\Models\Role::firstOrCreate([
                    'name' => 'super_admin',
                    'guard_name' => 'web',
                ]);
                $hasAnySuperAdmin = \Illuminate\Support\Facades\DB::table('model_has_roles')
                    ->where('role_id', $superRole->id)
                    ->exists();
                if (! $hasAnySuperAdmin) {
                    foreach (\App\Models\User::all() as $u) {
                        $u->assignRole($superRole);
                    }
                    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
                }
            }
        } catch (\Throwable $e) {
            // Ignore during CLI/bootstrap if DB is not ready
        }
    }
}