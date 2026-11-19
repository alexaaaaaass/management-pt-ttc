<?php

namespace App\Filament\Admin\Resources\AdminResource\Widgets;

use App\Models\InvPayment;
use App\Models\TransKas;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Schema;

class PemasukanChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Arus Kas Masuk (Pemasukan)';

    protected static ?int $sort = 5;

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

        $invPayments = [];
        if (Schema::hasTable('inv_payments')) {
            $invPayments = InvPayment::query()
                ->selectRaw('MONTH(tanggal_bayar) as bulan, SUM(nominal_bayar) as total')
                ->whereYear('tanggal_bayar', $currentYear)
                ->groupByRaw('MONTH(tanggal_bayar)')
                ->pluck('total', 'bulan')
                ->all();
        }

        $kasMasuks = [];
        if (Schema::hasTable('trans_kas')) {
            $kasMasuks = TransKas::query()
                ->selectRaw('MONTH(tanggal_transaksi) as bulan, SUM(nominal) as total')
                ->where('tipe_transaksi', 'KAS_MASUK')
                ->whereYear('tanggal_transaksi', $currentYear)
                ->groupByRaw('MONTH(tanggal_transaksi)')
                ->pluck('total', 'bulan')
                ->all();
        }

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $labels[] = date('M', mktime(0, 0, 0, $bulan, 1));
            $data[] = (float) (($invPayments[$bulan] ?? 0) + ($kasMasuks[$bulan] ?? 0));
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Pemasukan (IDR)',
                    'data' => $data,
                    'fill' => true,
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.15)',
                    'borderWidth' => 3,
                    'tension' => 0.45,
                    'pointBackgroundColor' => '#2563eb',
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
