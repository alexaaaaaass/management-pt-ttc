<?php

namespace App\Filament\Admin\Resources\AdminResource\Widgets;

use App\Models\InvPayment;
use App\Models\TransKas;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PemasukanChart extends ChartWidget
{
    protected static bool $isLazy = true;

    protected static ?string $heading = 'Grafik Pemasukan';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $year = now()->year;

        // 1 Query agregasi untuk seluruh bulan (menggantikan 12 query terpisah)
        $invoiceByMonth = InvPayment::query()
            ->selectRaw('MONTH(tanggal_bayar) as bulan, SUM(nominal_bayar) as total')
            ->whereYear('tanggal_bayar', $year)
            ->groupByRaw('MONTH(tanggal_bayar)')
            ->pluck('total', 'bulan');

        // 1 Query agregasi untuk kas masuk seluruh bulan (menggantikan 12 query terpisah)
        $kasMasukByMonth = TransKas::query()
            ->selectRaw('MONTH(tanggal_transaksi) as bulan, SUM(nominal) as total')
            ->where('tipe_transaksi', 'KAS_MASUK')
            ->whereYear('tanggal_transaksi', $year)
            ->groupByRaw('MONTH(tanggal_transaksi)')
            ->pluck('total', 'bulan');

        $labels = [];
        $data = [];

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $labels[] = date('M', mktime(0, 0, 0, $bulan, 1));
            $invoicePayment = (float) ($invoiceByMonth[$bulan] ?? 0);
            $kasMasuk = (float) ($kasMasukByMonth[$bulan] ?? 0);
            $data[] = $invoicePayment + $kasMasuk;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $data,
                    'backgroundColor' => '#22c55e',
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
