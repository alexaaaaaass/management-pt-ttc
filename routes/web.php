<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalesOrderPdfController;
use App\Http\Controllers\PurchaseRequestPdfController;
use App\Http\Controllers\PurchaseOrderPdfController;
use App\Http\Controllers\SubcountInPdfController;
use App\Models\PenerimaanBarang;
use App\Http\Controllers\SubcountOutPdfController;
use Barryvdh\DomPDF\Facade\Pdf;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get(
    '/sales-order/{salesOrder}/preview',
    [SalesOrderPdfController::class, 'preview']
)->name('sales-order.preview');

Route::get(
    '/sales-order/{salesOrder}/download',
    [SalesOrderPdfController::class, 'download']
)->name('sales-order.download');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ------------- PDF PURCHASE REQUEST ----------------
Route::get(
'/purchase-request/{purchaseRequest}/preview',
[PurchaseRequestPdfController::class,'preview']
)
->name('purchase-request.preview');


Route::get(
'/purchase-request/{purchaseRequest}/download',
[PurchaseRequestPdfController::class,'download']
)
->name('purchase-request.download');

// ------------ PDF PURCHASE ORDER ----------------
Route::get(
'/purchase-order/{purchaseOrder}/preview',
[PurchaseOrderPdfController::class,'preview']
)->name('purchase-order.preview');

Route::get(
'/purchase-order/{purchaseOrder}/download',
[PurchaseOrderPdfController::class,'download']
)->name('purchase-order.download');

// ------------ PDF SUBCOUNT IN ----------------
Route::get(
    '/subcount-in/{subcountIn}/preview',
    [SubcountInPdfController::class, 'preview']
)->name('subcount-in.preview');

Route::get(
    '/subcount-in/{subcountIn}/download',
    [SubcountInPdfController::class, 'download']
)->name('subcount-in.download');

// ----------- PDF SUBCOUNT OUT ----------------

Route::get(
    '/subcount-out/{subcountOut}/preview',
    [SubcountOutPdfController::class, 'preview']
)->name('subcount-out.preview');

Route::get(
    '/subcount-out/{subcountOut}/download',
    [SubcountOutPdfController::class, 'download']
)->name('subcount-out.download');

// -------------- PDF PENERIMAAN BARANG -----------------
Route::get('/penerimaan-barang/{id}/pdf', function ($id){

    $pb = PenerimaanBarang::with([
        'purchaseOrder.supplier',
        'items.purchaseOrderItem.item.satuan'
    ])->findOrFail($id);

    $pdf = Pdf::loadView(
        'pdf.penerimaan-barang',
        compact('pb')
    );

    return $pdf->stream();

})->name('pb.preview');

Route::get('/penerimaan-barang/{id}/download', function ($id) {

    $pb = PenerimaanBarang::findOrFail($id);

    dd([
        'id' => $pb->id,
        'no_penerimaan' => $pb->no_penerimaan,
        'items_count' => $pb->items()->count(),
    ]);

})->name('pb.download');


// Route::get('/penerimaan-barang/{id}/download', function($id){

//    $pb = PenerimaanBarang::with([
//     'purchaseOrder',
//     'purchaseOrder.supplier',
//     'items.purchaseOrderItem.item.satuan'
// ])->findOrFail($id);

// dd($pb->toArray());
//     return Pdf::loadView(
//         'pdf.penerimaan-barang',
//         compact('pb')
//     )->download($pb->no_penerimaan.'.pdf');

// })->name('pb.download');







require __DIR__.'/auth.php';