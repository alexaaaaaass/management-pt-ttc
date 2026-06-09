<?php

namespace App\Filament\Admin\Resources\AdminResource\Widgets;

use App\Models\PoBillPay;
use App\Models\OperasionalPay;
use App\Models\TransKas;
use Filament\Widgets\ChartWidget;

class PengeluaranChart extends ChartWidget
{
    protected static ?string $heading =
        'Grafik Pengeluaran';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        for ($bulan = 1; $bulan <= 12; $bulan++) {

            $labels[] = date(
                'M',
                mktime(0, 0, 0, $bulan, 1)
            );

            $poBill =
                PoBillPay::query()
                    ->whereMonth(
                        'tanggal_pembayaran',
                        $bulan
                    )
                    ->whereYear(
                        'tanggal_pembayaran',
                        now()->year
                    )
                    ->sum('total_pembayaran');

            $operasional =
                OperasionalPay::query()
                    ->whereMonth(
                        'tanggal_transaksi',
                        $bulan
                    )
                    ->whereYear(
                        'tanggal_transaksi',
                        now()->year
                    )
                    ->sum('nominal');

            $kasKeluar =
                TransKas::query()
                    ->where(
                        'tipe_transaksi',
                        'KAS_KELUAR'
                    )
                    ->whereMonth(
                        'tanggal_transaksi',
                        $bulan
                    )
                    ->whereYear(
                        'tanggal_transaksi',
                        now()->year
                    )
                    ->sum('nominal');

            $data[] =
                $poBill +
                $operasional +
                $kasKeluar;
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