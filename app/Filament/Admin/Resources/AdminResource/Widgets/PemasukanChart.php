<?php

namespace App\Filament\Admin\Resources\AdminResource\Widgets;

use App\Models\InvPayment;
use App\Models\TransKas;
use Filament\Widgets\ChartWidget;

class PemasukanChart extends ChartWidget
{
    protected static ?string $heading =
        'Grafik Pemasukan';

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

            $invoicePayment =
                InvPayment::query()
                    ->whereMonth(
                        'tanggal_bayar',
                        $bulan
                    )
                    ->whereYear(
                        'tanggal_bayar',
                        now()->year
                    )
                    ->sum('nominal_bayar');

            $kasMasuk =
                TransKas::query()
                    ->where(
                        'tipe_transaksi',
                        'KAS_MASUK'
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
                $invoicePayment +
                $kasMasuk;
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