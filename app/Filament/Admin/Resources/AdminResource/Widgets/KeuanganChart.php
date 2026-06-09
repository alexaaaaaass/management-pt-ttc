<?php

namespace App\Filament\Admin\Resources\AdminResource\Widgets;
use App\Models\InvPayment;
use App\Models\PoBillPay;
use App\Models\OperasionalPay;

use Filament\Widgets\ChartWidget;

class KeuanganChart extends ChartWidget
{
    protected static ?string $heading =
        'Grafik Pemasukan & Pengeluaran';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $labels = [];
        $pemasukan = [];
        $pengeluaran = [];

        for ($bulan = 1; $bulan <= 12; $bulan++) {

            $labels[] = date(
                'M',
                mktime(0, 0, 0, $bulan, 1)
            );

            /*
            |--------------------------------------------------------------------------
            | PEMASUKAN
            |--------------------------------------------------------------------------
            */

            $totalMasuk = InvPayment::query()
                ->whereMonth(
                    'tanggal_bayar',
                    $bulan
                )
                ->whereYear(
                    'tanggal_bayar',
                    now()->year
                )
                ->sum('nominal_bayar');

            /*
            |--------------------------------------------------------------------------
            | PENGELUARAN PO BILL
            |--------------------------------------------------------------------------
            */

            $poBill = PoBillPay::query()
                ->whereMonth(
                    'tanggal_pembayaran',
                    $bulan
                )
                ->whereYear(
                    'tanggal_pembayaran',
                    now()->year
                )
                ->sum('total_pembayaran');

            /*
            |--------------------------------------------------------------------------
            | PENGELUARAN OPERASIONAL
            |--------------------------------------------------------------------------
            */

            $operasional = OperasionalPay::query()
                ->whereMonth(
                    'tanggal_transaksi',
                    $bulan
                )
                ->whereYear(
                    'tanggal_transaksi',
                    now()->year
                )
                ->sum('nominal');

            $totalKeluar =
                $poBill + $operasional;

            $pemasukan[] = $totalMasuk;
            $pengeluaran[] = $totalKeluar;
        }

        return [

            'datasets' => [

                [
                    'label' => 'Pemasukan',
                    'data' => $pemasukan,
                    'backgroundColor' =>
                        'rgba(34,197,94,0.8)',
                ],

                [
                    'label' => 'Pengeluaran',
                    'data' => $pengeluaran,
                    'backgroundColor' =>
                        'rgba(239,68,68,0.8)',
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