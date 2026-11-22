<?php

namespace App\Filament\Admin\Widgets;

use App\Models\PracetakMaterialStock;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Schema;

class PracetakStockChart extends ChartWidget
{
    protected static ?string $heading = 'Posisi Saldo Stok Material Plate (Sheet)';

    protected static bool $isLazy = true;

    protected static ?string $maxHeight = '260px';

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
                    ->limit(10)
                    ->get();

                foreach ($stocks as $stock) {
                    $label = $stock->plate?->kode_material
                        ? ($stock->plate->kode_material . ' (' . ($stock->plate->brand ?: '-') . ')')
                        : ('Plate #' . $stock->master_material_plate_id);
                    $qty = round((float) $stock->stok_sheet_utuh, 2);

                    $labels[] = $label;
                    $data[] = $qty;
                    $colors[] = $qty <= 5 ? '#ef4444' : '#2563eb';
                }
            }
        } catch (\Throwable $e) {
        }

        if (empty($labels)) {
            $labels = ['Belum ada stok plate'];
            $data = [0];
            $colors = ['#64748b'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Saldo Tersedia (Sheet)',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderRadius' => 6,
                    'maxBarThickness' => 42,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => ['color' => 'rgba(148, 163, 184, 0.15)'],
                ],
                'x' => [
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
