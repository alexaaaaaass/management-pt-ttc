<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FinishGoodStock;
use App\Models\Invoice;
use App\Models\InvPayment;
use App\Models\MasterSpk;
use App\Models\MaterialStock;
use App\Models\PoBilling;
use App\Models\PoBillPay;
use App\Models\SalesOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Ringkasan KPI Dashboard Utama ERP TTC untuk Frontend Next.js.
     */
    public function summary(Request $request): JsonResponse
    {
        $totalTagihanInvoice = (float) Invoice::sum('grand_total');
        $totalPiutang = (float) Invoice::sum('sisa_tagihan');
        $totalPenerimaanInvoice = (float) InvPayment::where('status', 'SUCCESS')->sum('nominal_bayar');

        $totalTagihanPo = (float) PoBilling::sum('grand_total');
        $totalPembayaranPo = (float) PoBillPay::sum('total_pembayaran');
        $totalHutangPo = max(0.0, $totalTagihanPo - $totalPembayaranPo);

        $totalFinishGoodOnHand = (float) FinishGoodStock::sum('on_hand');
        $lowMaterialStockCount = MaterialStock::whereHas('item', function ($q) {
            $q->whereColumn('material_stocks.on_hand', '<', 'master_items.min_stock');
        })->count();

        $recentSpks = MasterSpk::with(['salesOrder.customer', 'salesOrder.itemable'])
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(function (MasterSpk $spk) {
                return [
                    'id'                => $spk->id,
                    'no_spk'            => $spk->no_spk,
                    'no_sales_order'    => $spk->salesOrder?->no_sales_order,
                    'customer'          => $spk->salesOrder?->customer?->nama_customer,
                    'product_name'      => $spk->salesOrder?->product_name,
                    'target_qty'        => (float) ($spk->salesOrder?->qty ?? 0),
                    'total_good_packed' => (float) $spk->total_good_packed,
                    'on_hand_stock'     => (float) $spk->on_hand_stock,
                    'status'            => $spk->status ?? 'In Progress',
                ];
            });

        return response()->json([
            'kpis' => [
                'total_tagihan_invoice'    => $totalTagihanInvoice,
                'total_penerimaan_invoice' => $totalPenerimaanInvoice,
                'total_piutang_customer'   => $totalPiutang,
                'total_hutang_supplier'    => $totalHutangPo,
                'total_pembayaran_po'      => $totalPembayaranPo,
                'total_sales_orders'       => SalesOrder::count(),
                'total_finish_good_stock'  => $totalFinishGoodOnHand,
                'low_material_stock_count' => $lowMaterialStockCount,
            ],
            'recent_spks' => $recentSpks,
        ]);
    }
}
