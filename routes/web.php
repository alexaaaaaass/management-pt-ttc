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
    return redirect('/admin');
});

Route::get('/login', function () {
    return redirect('/admin/login');
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
    )->name('suratjalan.download');

    Route::get(
        '/surat-jalan-pdf/{suratJalan}/preview',
        [SuratJalanPdfController::class, 'preview']
    )->name('surat-jalan.preview');

    Route::get(
        '/surat-jalan-pdf/{suratJalan}/download',
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

    /*
    |----------------------------------------------------------------------
    | Finance Voucher & Financial Reports PDF
    |----------------------------------------------------------------------
    */

    Route::get(
        '/finance/voucher/kas/{transKas}/preview',
        [\App\Http\Controllers\FinancePdfController::class, 'previewVoucherKas']
    )->name('finance.voucher.kas.preview');

    Route::get(
        '/finance/voucher/kas/{transKas}/download',
        [\App\Http\Controllers\FinancePdfController::class, 'downloadVoucherKas']
    )->name('finance.voucher.kas.download');

    Route::get(
        '/finance/voucher/bank/{transKasBank}/preview',
        [\App\Http\Controllers\FinancePdfController::class, 'previewVoucherBank']
    )->name('finance.voucher.bank.preview');

    Route::get(
        '/finance/voucher/bank/{transKasBank}/download',
        [\App\Http\Controllers\FinancePdfController::class, 'downloadVoucherBank']
    )->name('finance.voucher.bank.download');

    Route::get(
        '/finance/voucher/operasional/{operasionalPay}/preview',
        [\App\Http\Controllers\FinancePdfController::class, 'previewVoucherOperasional']
    )->name('finance.voucher.operasional.preview');

    Route::get(
        '/finance/voucher/operasional/{operasionalPay}/download',
        [\App\Http\Controllers\FinancePdfController::class, 'downloadVoucherOperasional']
    )->name('finance.voucher.operasional.download');

    Route::get(
        '/finance/commission/{orderId}/preview',
        [\App\Http\Controllers\FinancePdfController::class, 'previewCommissionRequest']
    )->name('finance.commission.preview');

    Route::get(
        '/finance/commission/{orderId}/download',
        [\App\Http\Controllers\FinancePdfController::class, 'downloadCommissionRequest']
    )->name('finance.commission.download');

    Route::get(
        '/finance/voucher/memorial/{journalEntry}/preview',
        [\App\Http\Controllers\FinancePdfController::class, 'previewVoucherMemorial']
    )->name('finance.voucher.memorial.preview');

    Route::get(
        '/finance/voucher/memorial/{journalEntry}/download',
        [\App\Http\Controllers\FinancePdfController::class, 'downloadVoucherMemorial']
    )->name('finance.voucher.memorial.download');

    Route::get(
        '/finance/report/laba-rugi/preview',
        [\App\Http\Controllers\FinancePdfController::class, 'previewLabaRugi']
    )->name('finance.report.laba-rugi.preview');

    Route::get(
        '/finance/report/laba-rugi/download',
        [\App\Http\Controllers\FinancePdfController::class, 'downloadLabaRugi']
    )->name('finance.report.laba-rugi.download');

    Route::get(
        '/finance/report/laba-rugi-mesin/preview',
        [\App\Http\Controllers\FinancePdfController::class, 'previewLabaRugiMesin']
    )->name('finance.report.laba-rugi-mesin.preview');

    Route::get(
        '/finance/report/laba-rugi-mesin/download',
        [\App\Http\Controllers\FinancePdfController::class, 'downloadLabaRugiMesin']
    )->name('finance.report.laba-rugi-mesin.download');

    Route::get(
        '/finance/report/neraca/preview',
        [\App\Http\Controllers\FinancePdfController::class, 'previewNeraca']
    )->name('finance.report.neraca.preview');

    Route::get(
        '/finance/report/neraca/download',
        [\App\Http\Controllers\FinancePdfController::class, 'downloadNeraca']
    )->name('finance.report.neraca.download');

    Route::get(
        '/finance/report/general-ledger/preview',
        [\App\Http\Controllers\FinancePdfController::class, 'previewGeneralLedger']
    )->name('finance.report.general-ledger.preview');

    Route::get(
        '/finance/report/general-ledger/download',
        [\App\Http\Controllers\FinancePdfController::class, 'downloadGeneralLedger']
    )->name('finance.report.general-ledger.download');

    Route::get(
        '/finance/report/trial-balance/preview',
        [\App\Http\Controllers\FinancePdfController::class, 'previewTrialBalance']
    )->name('finance.report.trial-balance.preview');

    Route::get(
        '/finance/report/trial-balance/download',
        [\App\Http\Controllers\FinancePdfController::class, 'downloadTrialBalance']
    )->name('finance.report.trial-balance.download');

    // Route alias / redirect untuk Master COA agar URL /admin/master-c-o-a-s tidak pernah 404
    Route::redirect('/admin/master-c-o-a-s', '/admin/master-c-o-as');

});

require __DIR__ . '/auth.php';