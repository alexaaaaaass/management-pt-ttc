<?php

namespace App\Filament\Admin\Widgets;

use App\Models\PracetakMaterialStock;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Schema;

class PracetakStockChart extends ChartWidget
{
    protected static ?string $heading = 'Saldo Stok Plate (Sheet)';

    protected static bool $isLazy = true;

    protected static ?string $maxHeight = '185px';

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected function getData(): array
    {
        $labels = [];
        $data = [];
        $colors = [];

        try {
            if (Schema::hasTable('pracetak_material_stocks')) {
                $stocks = PracetakMaterialStock::query()
                    ->with('plate')
                    ->orderByDesc('stok_sheet_utuh')
                    ->limit(6)
                    ->get();

                foreach ($stocks as $stock) {
                    $brand = trim((string) ($stock->plate?->brand ?? ''));
                    $kode = trim((string) ($stock->plate?->kode_material ?? ''));
                    $label = $brand !== '' ? $brand : ($kode !== '' ? $kode : ('Plate #' . $stock->master_material_plate_id));
                    $qty = round((float) $stock->stok_sheet_utuh, 2);

                    $labels[] = \Illuminate\Support\Str::limit($label, 22);
                    $data[] = $qty;
                    $colors[] = $qty <= 5 ? 'rgba(239, 68, 68, 0.78)' : 'rgba(59, 130, 246, 0.72)';
                }
            }
        } catch (\Throwable $e) {
        }

        if (empty($labels)) {
            $labels = ['Belum ada stok plate'];
            $data = [0];
            $colors = ['rgba(148, 163, 184, 0.5)'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Sheet',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderRadius' => 5,
                    'borderSkipped' => false,
                    'maxBarThickness' => 16,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'border' => ['display' => false],
                    'ticks' => ['color' => 'rgba(148, 163, 184, 0.7)', 'font' => ['size' => 11]],
                    'grid' => ['color' => 'rgba(148, 163, 184, 0.08)'],
                ],
                'y' => [
                    'border' => ['display' => false],
                    'ticks' => ['color' => 'rgba(148, 163, 184, 0.88)', 'font' => ['size' => 11]],
                    'grid' => ['display' => false],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
