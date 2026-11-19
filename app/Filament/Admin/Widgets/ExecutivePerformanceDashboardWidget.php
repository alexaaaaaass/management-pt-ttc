<?php

namespace App\Filament\Admin\Widgets;

use App\Models\PenerimaanBarang;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\SuratJalan;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExecutivePerformanceDashboardWidget extends Widget
{
    protected static string $view = 'filament.admin.widgets.executive-performance-dashboard-widget';

    protected static ?int $sort = 0;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public int $year;

    public array $kpis = [
        'total_order' => '0 BIO',
        'total_kirim' => '0 BIO',
        'total_po'    => '0 BIO',
        'total_pb'    => '0 BIO',
    ];

    public array $salesMonthly = [];
    public array $purchaseMonthly = [];
    public array $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    public function mount(): void
    {
        $this->year = (int) (request()->query('year') ?: now()->year);
        $this->loadData();
    }

    public function searchYear(): void
    {
        if ($this->year < 2000 || $this->year > 2099) {
            $this->year = (int) now()->year;
        }
        $this->loadData();
        $this->dispatch('dashboard-data-updated', [
            'sales'    => $this->salesMonthly,
            'purchase' => $this->purchaseMonthly,
            'months'   => $this->months,
            'year'     => $this->year,
        ]);
    }

    public function loadData(): void
    {
        $year = $this->year;

        // 1. TOTAL ORDER (Sales Orders)
        $totalOrderVal = 0.0;
        $monthlySales = array_fill(1, 12, 0.0);

        try {
            if (Schema::hasTable('sales_orders')) {
                $orders = SalesOrder::with('items')
                    ->where(function ($q) use ($year) {
                        $q->whereYear('tanggal_po', $year)
                          ->orWhereYear('created_at', $year);
                    })
                    ->get();

                foreach ($orders as $so) {
                    $val = 0.0;
                    if ($so->order_type === 'SPK') {
                        $val = (float) ($so->qty ?? 0) * (float) ($so->harga_pcs ?? 0);
                    } else {
                        $val = (float) $so->items->sum(fn ($it) => (float) ($it->qty ?? 0) * (float) ($it->harga ?? 0));
                    }
                    $totalOrderVal += $val;

                    $m = 0;
                    if ($so->tanggal_po) {
                        $m = (int) date('n', strtotime((string) $so->tanggal_po));
                    } elseif ($so->created_at) {
                        $m = (int) $so->created_at->format('n');
                    }
                    if ($m >= 1 && $m <= 12) {
                        $monthlySales[$m] += $val;
                    }
                }
            }
        } catch (\Throwable $e) {}

        // 2. TOTAL KIRIM (Surat Jalans)
        $totalKirimVal = 0.0;
        try {
            if (Schema::hasTable('surat_jalans')) {
                $sjs = SuratJalan::with('salesOrder')
                    ->whereYear('tanggal_surat_jalan', $year)
                    ->get();

                foreach ($sjs as $sj) {
                    if ($sj->salesOrder) {
                        $harga = (float) ($sj->salesOrder->harga_pcs ?? 0);
                        $totalKirimVal += ((float) ($sj->qty_pengiriman ?? 0) * $harga);
                    }
                }
            }
        } catch (\Throwable $e) {}

        // 3. TOTAL PO (Purchase Orders)
        $totalPoVal = 0.0;
        $monthlyPurchase = array_fill(1, 12, 0.0);
        try {
            if (Schema::hasTable('purchase_orders')) {
                $pos = PurchaseOrder::with('items')
                    ->where(function ($q) use ($year) {
                        $q->whereYear('po_date', $year)
                          ->orWhereYear('created_at', $year);
                    })
                    ->get();

                foreach ($pos as $po) {
                    $val = (float) $po->items->sum(fn ($it) => (float) ($it->qty ?? 0) * (float) ($it->harga ?? 0));
                    $totalPoVal += $val;

                    $m = 0;
                    if ($po->po_date) {
                        $m = (int) date('n', strtotime((string) $po->po_date));
                    } elseif ($po->created_at) {
                        $m = (int) $po->created_at->format('n');
                    }
                    if ($m >= 1 && $m <= 12) {
                        $monthlyPurchase[$m] += $val;
                    }
                }
            }
        } catch (\Throwable $e) {}

        // 4. TOTAL PB (Penerimaan Barang)
        $totalPbVal = 0.0;
        try {
            if (Schema::hasTable('penerimaan_barangs') && Schema::hasTable('penerimaan_barang_items')) {
                $pbs = DB::table('penerimaan_barang_items')
                    ->join('penerimaan_barangs', 'penerimaan_barang_items.penerimaan_barang_id', '=', 'penerimaan_barangs.id')
                    ->join('purchase_order_items', 'penerimaan_barang_items.purchase_order_item_id', '=', 'purchase_order_items.id')
                    ->whereYear('penerimaan_barangs.tanggal_terima', $year)
                    ->select(DB::raw('SUM(penerimaan_barang_items.qty_terima * purchase_order_items.harga) as total'))
                    ->value('total');

                $totalPbVal = (float) ($pbs ?? 0);
            }
        } catch (\Throwable $e) {}

        $this->kpis = [
            'total_order' => $this->formatBio($totalOrderVal),
            'total_kirim' => $this->formatBio($totalKirimVal),
            'total_po'    => $this->formatBio($totalPoVal),
            'total_pb'    => $this->formatBio($totalPbVal),
        ];

        $this->salesMonthly = array_values($monthlySales);
        $this->purchaseMonthly = array_values($monthlyPurchase);
    }

    private function formatBio(float $val): string
    {
        if ($val >= 1000000000) {
            return number_format($val / 1000000000, 1, '.', '') . ' BIO';
        }
        if ($val >= 1000000) {
            return number_format($val / 1000000, 1, '.', '') . ' JUTA';
        }
        if ($val == 0.0) {
            return '0 BIO';
        }
        return number_format($val, 0, ',', '.');
    }
}
