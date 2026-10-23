<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvoicePdfController;
use App\Http\Controllers\SuratJalanPdfController;
use App\Http\Controllers\SalesOrderPdfController;
use App\Http\Controllers\PurchaseRequestPdfController;
use App\Http\Controllers\PurchaseOrderPdfController;
use App\Http\Controllers\SubcountInPdfController;
use App\Http\Controllers\SubcountOutPdfController;
use App\Http\Controllers\ExternalReturnPdfController;
use App\Http\Controllers\IMRPrintingPdfController;
use App\Http\Controllers\PenerimaanBarangPdfController;
use App\Http\Controllers\BeritaAcaraController;
use App\Http\Controllers\BeritaAcaraPdfController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| All authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |----------------------------------------------------------------------
    | Profile
    |----------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |----------------------------------------------------------------------
    | Berita Acara (Resource + PDF)
    |----------------------------------------------------------------------
    */

    Route::get('/berita-acaras/{beritaAcara}/pdf', [BeritaAcaraPdfController::class, 'preview'])
        ->name('berita-acaras.pdf');

    Route::resource('berita-acaras', BeritaAcaraController::class);

    /*
    |----------------------------------------------------------------------
    | Invoice PDF
    |----------------------------------------------------------------------
    */

    Route::get(
        '/invoice/{invoice}/preview',
        [InvoicePdfController::class, 'preview']
    )->name('invoice.preview');

    Route::get(
        '/invoice/{invoice}/download',
        [InvoicePdfController::class, 'download']
    )->name('invoice.download');

    /*
    |----------------------------------------------------------------------
    | Surat Jalan PDF
    |----------------------------------------------------------------------
    */

    Route::get(
        '/surat-jalan/{suratJalan}/preview',
        [SuratJalanPdfController::class, 'preview']
    )->name('suratjalan.preview');

    Route::get(
        '/surat-jalan/{suratJalan}/download',
        [SuratJalanPdfController::class, 'download']
    )->name('surat-jalan.download');

    /*
    |----------------------------------------------------------------------
    | Sales Order PDF
    |----------------------------------------------------------------------
    */

    Route::get(
        '/sales-order/{salesOrder}/preview',
        [SalesOrderPdfController::class, 'preview']
    )->name('sales-order.preview');

    Route::get(
        '/sales-order/{salesOrder}/download',
        [SalesOrderPdfController::class, 'download']
    )->name('sales-order.download');

    /*
    |----------------------------------------------------------------------
    | Approval Form PDF
    |----------------------------------------------------------------------
    */

    Route::get(
        '/approval-form/{approvalForm}/preview',
        [\App\Http\Controllers\ApprovalFormPdfController::class, 'preview']
    )->name('approval-form.preview');

    Route::get(
        '/approval-form/{approvalForm}/download',
        [\App\Http\Controllers\ApprovalFormPdfController::class, 'download']
    )->name('approval-form.download');

    /*
    |----------------------------------------------------------------------
    | Order Confirmation PDF
    |----------------------------------------------------------------------
    */

    Route::get(
        '/order-confirmation/{orderConfirmation}/preview',
        [\App\Http\Controllers\OrderConfirmationPdfController::class, 'preview']
    )->name('order-confirmation.preview');

    Route::get(
        '/order-confirmation/{orderConfirmation}/download',
        [\App\Http\Controllers\OrderConfirmationPdfController::class, 'download']
    )->name('order-confirmation.download');

    /*
    |----------------------------------------------------------------------
    | Purchase Request PDF
    |----------------------------------------------------------------------
    */

    Route::get(
        '/purchase-request/{purchaseRequest}/preview',
        [PurchaseRequestPdfController::class, 'preview']
    )->name('purchase-request.preview');

    Route::get(
        '/purchase-request/{purchaseRequest}/download',
        [PurchaseRequestPdfController::class, 'download']
    )->name('purchase-request.download');

    /*
    |----------------------------------------------------------------------
    | Purchase Order PDF
    |----------------------------------------------------------------------
    */

    Route::get(
        '/purchase-order/{purchaseOrder}/preview',
        [PurchaseOrderPdfController::class, 'preview']
    )->name('purchase-order.preview');

    Route::get(
        '/purchase-order/{purchaseOrder}/download',
        [PurchaseOrderPdfController::class, 'download']
    )->name('purchase-order.download');

    /*
    |----------------------------------------------------------------------
    | Subcount In PDF
    |----------------------------------------------------------------------
    */

    Route::get(
        '/subcount-in/{subcountIn}/preview',
        [SubcountInPdfController::class, 'preview']
    )->name('subcount-in.preview');

    Route::get(
        '/subcount-in/{subcountIn}/download',
        [SubcountInPdfController::class, 'download']
    )->name('subcount-in.download');

    /*
    |----------------------------------------------------------------------
    | Subcount Out PDF
    |----------------------------------------------------------------------
    */

    Route::get(
        '/subcount-out/{subcountOut}/preview',
        [SubcountOutPdfController::class, 'preview']
    )->name('subcount-out.preview');

    Route::get(
        '/subcount-out/{subcountOut}/download',
        [SubcountOutPdfController::class, 'download']
    )->name('subcount-out.download');

    /*
    |----------------------------------------------------------------------
    | Penerimaan Barang PDF
    |----------------------------------------------------------------------
    */

    Route::get(
        '/penerimaan-barang/{penerimaanBarang}/preview',
        [PenerimaanBarangPdfController::class, 'preview']
    )->name('pb.preview');

    Route::get(
        '/penerimaan-barang/{penerimaanBarang}/download',
        [PenerimaanBarangPdfController::class, 'download']
    )->name('pb.download');

    /*
    |----------------------------------------------------------------------
    | External Return PDF
    |----------------------------------------------------------------------
    */

    Route::get(
        '/external-return/{externalReturn}/preview',
        [ExternalReturnPdfController::class, 'preview']
    )->name('external-return.preview');

    Route::get(
        '/external-return/{externalReturn}/download',
        [ExternalReturnPdfController::class, 'download']
    )->name('external-return.download');

    /*
    |----------------------------------------------------------------------
    | IMR Printing PDF
    |----------------------------------------------------------------------
    */

    Route::get(
        '/imr/{imr}/preview',
        [IMRPrintingPdfController::class, 'preview']
    )->name('imr.preview');

});

require __DIR__ . '/auth.php';