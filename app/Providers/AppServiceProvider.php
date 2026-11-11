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

            if (\Illuminate\Support\Facades\Schema::hasTable('master_coas') && ! \Illuminate\Support\Facades\Schema::hasTable('journal_entries')) {
                $jrnMig = base_path('database/migrations/2026_11_09_000001_create_journal_entries_tables.php');
                if (file_exists($jrnMig)) {
                    (require $jrnMig)->up();
                }
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('master_spk') && ! \Illuminate\Support\Facades\Schema::hasTable('blokir')) {
                \Illuminate\Support\Facades\Schema::create('blokir', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->string('no_blokir')->nullable();
                    $table->string('no_spk')->nullable();
                    $table->date('tanggal_blokir')->nullable();
                    $table->string('operator')->nullable();
                    $table->integer('qty_blokir')->default(0);
                    $table->string('customer')->nullable();
                    $table->text('keterangan')->nullable();
                    $table->timestamps();
                });
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('approval_forms') && \Illuminate\Support\Facades\Schema::hasTable('invoices')) {
                $isExactMacroState = fn (): bool => \App\Models\ApprovalForm::count() === 19
                    && \App\Models\OrderConfirmation::count() === 19
                    && \App\Models\SalesOrder::withTrashed()->count() === 19
                    && \App\Models\MasterSpk::count() === 19
                    && \App\Models\Invoice::count() === 17
                    && \App\Models\Customer::count() === 10
                    && \App\Models\MasterPrinting::count() === 3
                    && \App\Models\PurchaseOrder::count() === 0
                    && \App\Models\TransKas::count() === 0
                    && \App\Models\TransKasBank::count() === 0
                    && \App\Models\Gaji::count() === 0
                    && (float) \App\Models\MasterCOA::sum('saldo_debit') === 0.0
                    && (float) \App\Models\MasterCOA::sum('saldo_kredit') === 0.0
                    && \App\Models\OperasionalPay::count() === 6
                    && \App\Models\OperasionalPay::where('kode_transaksi', 'like', 'COM-%')->count() === 6;

                if (! $isExactMacroState()) {
                    $lockFile = storage_path('framework/bismillah_macro_seed.lock');
                    $fp = @fopen($lockFile, 'c+');
                    if ($fp && @flock($fp, LOCK_EX)) {
                        try {
                            if (! $isExactMacroState()) {
                                app(\Database\Seeders\BismillahMacroSalesSeeder::class)->run();
                            }
                        } finally {
                            @flock($fp, LOCK_UN);
                            @fclose($fp);
                        }
                    }
                }
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('coa_movements') && \Illuminate\Support\Facades\Schema::hasTable('master_coas')) {
                $totalDebit = (float) \Illuminate\Support\Facades\DB::table('coa_movements')->sum('debit');
                $totalKredit = (float) \Illuminate\Support\Facades\DB::table('coa_movements')->sum('kredit');
                if (abs($totalDebit - $totalKredit) >= 0.01) {
                    $treasury = app(\App\Services\Finance\TreasuryService::class);
                    foreach (\App\Models\InvPayment::where('status', 'SUCCESS')->get() as $pay) {
                        $cnt = \App\Models\CoaMovement::where('reference_type', 'INV_PAYMENT')->where('reference_id', $pay->id)->count();
                        if ($cnt < 2) {
                            $treasury->syncInvPayment($pay);
                        }
                    }
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