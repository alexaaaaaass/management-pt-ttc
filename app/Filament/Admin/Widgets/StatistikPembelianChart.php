<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StatistikPembelianChart extends ChartWidget
{
    protected static ?string $heading = 'Statistik Pembelian (IDR)';

    protected static ?int $sort = 4;

    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected static ?string $maxHeight = '280px';

    protected function getFilters(): ?array
    {
        $currentYear = (int) now()->year;

        return [
            (string) $currentYear => "Tahun $currentYear",
            (string) ($currentYear - 1) => "Tahun " . ($currentYear - 1),
        ];
    }

    protected function getData(): array
    {
        $year = (int) ($this->filter ?: now()->year);
        $monthlyPurchase = array_fill(1, 12, 0.0);

        try {
            if (Schema::hasTable('purchase_orders') && Schema::hasTable('purchase_order_items')) {
                $records = DB::table('purchase_order_items')
                    ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
                    ->where(function ($q) use ($year) {
                        $q->whereYear('purchase_orders.po_date', $year)
                          ->orWhereYear('purchase_orders.created_at', $year);
                    })
                    ->selectRaw('
                        CASE 
                            WHEN purchase_orders.po_date IS NOT NULL THEN MONTH(purchase_orders.po_date)
                            ELSE MONTH(purchase_orders.created_at)
                        END as bulan,
                        SUM(COALESCE(purchase_order_items.total, COALESCE(purchase_order_items.qty_po, 0) * COALESCE(purchase_order_items.price, 0))) as total
                    ')
                    ->groupBy('bulan')
                    ->pluck('total', 'bulan');

                foreach ($records as $bulan => $total) {
                    $b = (int) $bulan;
                    if ($b >= 1 && $b <= 12) {
                        $monthlyPurchase[$b] = (float) $total;
                    }
                }
            }
        } catch (\Throwable $e) {}

        $labels = [];
        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $labels[] = date('M', mktime(0, 0, 0, $m, 1));
            $data[] = $monthlyPurchase[$m];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pembelian (IDR)',
                    'data' => $data,
                    'backgroundColor' => '#f43f5e',
                    'hoverBackgroundColor' => '#e11d48',
                    'borderRadius' => 6,
                    'borderSkipped' => false,
                    'maxBarThickness' => 36,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
