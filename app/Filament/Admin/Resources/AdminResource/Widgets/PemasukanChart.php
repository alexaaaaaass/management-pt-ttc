<?php

namespace App\Filament\Admin\Resources\AdminResource\Widgets;

use App\Models\InvPayment;
use App\Models\TransKas;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Schema;

class PemasukanChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Arus Kas Masuk (Pemasukan)';

    protected static ?int $sort = 4;

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
                    'label' => 'Total Pemasukan',
                    'data' => $data,
                    'backgroundColor' => '#2563eb',
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
