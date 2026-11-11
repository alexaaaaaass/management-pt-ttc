<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Costing\HppCalculatorController;
use App\Http\Controllers\Api\V1\DashboardController;
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
    });
});
