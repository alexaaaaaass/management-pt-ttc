<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Costing\HppCalculatorController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\Marketing\ApprovalFormController;
use App\Http\Controllers\Api\V1\Marketing\OrderConfirmationController;
use App\Http\Controllers\Api\V1\Marketing\SalesOrderController;
use App\Http\Controllers\Api\V1\Master\CategoryItemController;
use App\Http\Controllers\Api\V1\Master\CustomerController;
use App\Http\Controllers\Api\V1\Master\FinishGoodItemController;
use App\Http\Controllers\Api\V1\Master\MasterItemController;
use App\Http\Controllers\Api\V1\Master\SatuanController;
use App\Http\Controllers\Api\V1\Master\SupplierController;
use App\Http\Controllers\Api\V1\Master\TypeItemController;
use App\Http\Controllers\Api\V1\System\RoleController;
use App\Http\Controllers\Api\V1\System\UserController;
use App\Http\Controllers\Api\V1\Warehouse\FinishGoodStockController;
use App\Http\Controllers\Api\V1\Warehouse\MaterialStockController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (TTC Manufacturing ERP - Decoupled Next.js Backend)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->name('api.v1.')->group(function () {

    // =========================================================================
    // PUBLIC AUTH ROUTES
    // =========================================================================
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

    // =========================================================================
    // PROTECTED ERP API ROUTES (Sanctum Token / SPA Session)
    // =========================================================================
    Route::middleware('auth:sanctum')->group(function () {

        // Auth & RBAC Profile
        Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        // Dashboard Overview & KPIs
        Route::get('/dashboard/summary', [DashboardController::class, 'summary'])->name('dashboard.summary');

        // Costing & Flexo Zonten HPP Calculator
        Route::post('/costing/flexo-hpp/calculate', [HppCalculatorController::class, 'calculate'])
            ->name('costing.flexo-hpp.calculate');

        // Warehouse: Material Stocks & Finish Good Stocks
        Route::prefix('warehouse')->name('warehouse.')->group(function () {
            Route::get('/material-stocks', [MaterialStockController::class, 'index'])->name('material-stocks.index');
            Route::get('/material-stocks/{materialStock}/track', [MaterialStockController::class, 'track'])->name('material-stocks.track');

            Route::get('/finish-good-stocks', [FinishGoodStockController::class, 'index'])->name('finish-good-stocks.index');
            Route::get('/finish-good-stocks/{finishGoodStock}/track', [FinishGoodStockController::class, 'track'])->name('finish-good-stocks.track');
            Route::post('/finish-good-stocks/{finishGoodStock}/adjust', [FinishGoodStockController::class, 'adjust'])->name('finish-good-stocks.adjust');
            Route::get('/finish-good-ledgers', [FinishGoodStockController::class, 'ledgers'])->name('finish-good-ledgers.index');
        });

        // Master Data (Fase 2)
        Route::prefix('master')->name('master.')->group(function () {
            Route::apiResource('/customers', CustomerController::class)->except(['show']);
            Route::apiResource('/suppliers', SupplierController::class)->except(['show']);
            Route::apiResource('/satuans', SatuanController::class)->except(['show']);
            Route::apiResource('/categories', CategoryItemController::class)->except(['show']);
            Route::apiResource('/types', TypeItemController::class)->except(['show']);

            Route::get('/items/options', [MasterItemController::class, 'options'])->name('items.options');
            Route::apiResource('/items', MasterItemController::class)
                ->parameters(['items' => 'masterItem'])
                ->except(['show']);

            Route::get('/finish-good-items/options', [FinishGoodItemController::class, 'options'])->name('finish-good-items.options');
            Route::post('/finish-good-items/{id}/restore', [FinishGoodItemController::class, 'restore'])->name('finish-good-items.restore');
            Route::apiResource('/finish-good-items', FinishGoodItemController::class);
        });

        // System & RBAC Management (Fase 2)
        Route::prefix('system')->name('system.')->group(function () {
            Route::apiResource('/users', UserController::class)->except(['show']);
            Route::get('/permissions', [RoleController::class, 'permissions'])->name('permissions.index');
            Route::apiResource('/roles', RoleController::class)->except(['show']);
        });

        // Marketing End-to-End Workflow (Fase 3)
        Route::prefix('marketing')->name('marketing.')->group(function () {
            Route::get('/approval-forms/options', [ApprovalFormController::class, 'options'])->name('approval-forms.options');
            Route::post('/approval-forms/{approvalForm}/convert-to-oc', [ApprovalFormController::class, 'convertToOc'])->name('approval-forms.convert-to-oc');
            Route::apiResource('/approval-forms', ApprovalFormController::class)->except(['show']);

            Route::post('/order-confirmations/{orderConfirmation}/convert-to-so', [OrderConfirmationController::class, 'convertToSo'])->name('order-confirmations.convert-to-so');
            Route::apiResource('/order-confirmations', OrderConfirmationController::class)->except(['store']);

            Route::get('/sales-orders/options', [SalesOrderController::class, 'options'])->name('sales-orders.options');
            Route::apiResource('/sales-orders', SalesOrderController::class)->except(['show']);
        });
    });
});
