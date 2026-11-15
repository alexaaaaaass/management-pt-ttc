<?php

namespace App\Filament\Admin\Resources\AdminResource\Widgets;

use App\Models\OperasionalPay;
use App\Models\PoBillPay;
use App\Models\TransKas;
use Filament\Widgets\ChartWidget;

class PengeluaranChart extends ChartWidget
{
    protected static bool $isLazy = true;

    protected static ?string $heading = 'Grafik Pengeluaran';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $year = now()->year;

        // 1 Query agregasi PO Bill Pay per bulan (menggantikan 12 query terpisah)
        $poBillByMonth = PoBillPay::query()
            ->selectRaw('MONTH(tanggal_pembayaran) as bulan, SUM(total_pembayaran) as total')
            ->whereYear('tanggal_pembayaran', $year)
            ->groupByRaw('MONTH(tanggal_pembayaran)')
            ->pluck('total', 'bulan');

        // 1 Query agregasi Operasional Pay per bulan (menggantikan 12 query terpisah)
        $operasionalByMonth = OperasionalPay::query()
            ->selectRaw('MONTH(tanggal_transaksi) as bulan, SUM(nominal) as total')
            ->whereYear('tanggal_transaksi', $year)
            ->groupByRaw('MONTH(tanggal_transaksi)')
            ->pluck('total', 'bulan');

        // 1 Query agregasi Kas Keluar per bulan (menggantikan 12 query terpisah)
        $kasKeluarByMonth = TransKas::query()
            ->selectRaw('MONTH(tanggal_transaksi) as bulan, SUM(nominal) as total')
            ->where('tipe_transaksi', 'KAS_KELUAR')
            ->whereYear('tanggal_transaksi', $year)
            ->groupByRaw('MONTH(tanggal_transaksi)')
            ->pluck('total', 'bulan');

        $labels = [];
        $data = [];

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $labels[] = date('M', mktime(0, 0, 0, $bulan, 1));
            $poBill = (float) ($poBillByMonth[$bulan] ?? 0);
            $operasional = (float) ($operasionalByMonth[$bulan] ?? 0);
            $kasKeluar = (float) ($kasKeluarByMonth[$bulan] ?? 0);

            $data[] = $poBill + $operasional + $kasKeluar;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pengeluaran',
                    'data' => $data,
                    'backgroundColor' => '#ef4444',
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
