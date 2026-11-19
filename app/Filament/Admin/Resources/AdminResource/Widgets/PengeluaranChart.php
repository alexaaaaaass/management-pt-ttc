<?php

namespace App\Filament\Admin\Resources\AdminResource\Widgets;

use App\Models\OperasionalPay;
use App\Models\PoBillPay;
use App\Models\TransKas;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Schema;

class PengeluaranChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Arus Kas Keluar (Pengeluaran)';

    protected static ?int $sort = 6;

    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected function getData(): array
    {
        $currentYear = now()->year;
        $labels = [];
        $data = [];

        $poBills = [];
        if (Schema::hasTable('po_bill_pays')) {
            $poBills = PoBillPay::query()
                ->selectRaw('MONTH(tanggal_pembayaran) as bulan, SUM(total_pembayaran) as total')
                ->whereYear('tanggal_pembayaran', $currentYear)
                ->groupByRaw('MONTH(tanggal_pembayaran)')
                ->pluck('total', 'bulan')
                ->all();
        }

        $operasionals = [];
        if (Schema::hasTable('operasional_pays')) {
            $operasionals = OperasionalPay::query()
                ->selectRaw('MONTH(tanggal_transaksi) as bulan, SUM(nominal) as total')
                ->whereYear('tanggal_transaksi', $currentYear)
                ->groupByRaw('MONTH(tanggal_transaksi)')
                ->pluck('total', 'bulan')
                ->all();
        }

        $kasKeluars = [];
        if (Schema::hasTable('trans_kas')) {
            $kasKeluars = TransKas::query()
                ->selectRaw('MONTH(tanggal_transaksi) as bulan, SUM(nominal) as total')
                ->where('tipe_transaksi', 'KAS_KELUAR')
                ->whereYear('tanggal_transaksi', $currentYear)
                ->groupByRaw('MONTH(tanggal_transaksi)')
                ->pluck('total', 'bulan')
                ->all();
        }

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $labels[] = date('M', mktime(0, 0, 0, $bulan, 1));
            $data[] = (float) (($poBills[$bulan] ?? 0) + ($operasionals[$bulan] ?? 0) + ($kasKeluars[$bulan] ?? 0));
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Pengeluaran (IDR)',
                    'data' => $data,
                    'fill' => true,
                    'borderColor' => '#f43f5e',
                    'backgroundColor' => 'rgba(244, 63, 94, 0.15)',
                    'borderWidth' => 3,
                    'tension' => 0.45,
                    'pointBackgroundColor' => '#f43f5e',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 7,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'animation' => [
                'duration' => 2000,
                'easing' => 'easeOutQuart',
            ],
            'animations' => [
                'y' => [
                    'easing' => 'easeOutQuart',
                    'duration' => 2000,
                    'from' => 0,
                ],
            ],
            'elements' => [
                'line' => [
                    'tension' => 0.45,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => '#f1f5f9',
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
