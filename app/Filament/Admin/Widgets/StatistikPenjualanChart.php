<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StatistikPenjualanChart extends ChartWidget
{
    protected static ?string $heading = 'Statistik Penjualan (IDR)';

    protected static ?int $sort = 3;

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
        $monthlySales = array_fill(1, 12, 0.0);

        try {
            if (Schema::hasTable('sales_orders')) {
                $records = DB::table('sales_orders')
                    ->whereNull('deleted_at')
                    ->where(function ($q) use ($year) {
                        $q->whereYear('tanggal_po', $year)
                          ->orWhereYear('created_at', $year);
                    })
                    ->selectRaw('
                        CASE 
                            WHEN tanggal_po IS NOT NULL THEN MONTH(tanggal_po)
                            ELSE MONTH(created_at)
                        END as bulan,
                        SUM(COALESCE(qty, 0) * COALESCE(harga_pcs, 0)) as total
                    ')
                    ->groupBy('bulan')
                    ->pluck('total', 'bulan');

                foreach ($records as $bulan => $total) {
                    $b = (int) $bulan;
                    if ($b >= 1 && $b <= 12) {
                        $monthlySales[$b] = (float) $total;
                    }
                }
            }
        } catch (\Throwable $e) {}

        $labels = [];
        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $labels[] = date('M', mktime(0, 0, 0, $m, 1));
            $data[] = $monthlySales[$m];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Penjualan (IDR)',
                    'data' => $data,
                    'backgroundColor' => '#4f46e5',
                    'hoverBackgroundColor' => '#4338ca',
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
