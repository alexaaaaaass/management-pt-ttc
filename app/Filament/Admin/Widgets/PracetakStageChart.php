<?php

namespace App\Filament\Admin\Widgets;

use App\Models\PracetakProcess;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Schema;

class PracetakStageChart extends ChartWidget
{
    protected static ?string $heading = 'Proses per Tahap';

    protected static bool $isLazy = true;

    protected static ?string $maxHeight = '185px';

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected function getData(): array
    {
        $counts = [
            'design'      => 0,
            'prove_print' => 0,
            'plat_making' => 0,
            'selesai'     => 0,
            'handed_over' => 0,
        ];

        try {
            if (Schema::hasTable('pracetak_processes')) {
                $rows = PracetakProcess::query()
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status');

                foreach ($rows as $status => $total) {
                    $key = $status === 'trial_design' ? 'design' : (string) $status;
                    if (array_key_exists($key, $counts)) {
                        $counts[$key] += (int) $total;
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        return [
            'datasets' => [
                [
                    'label' => 'Proses',
                    'data' => [
                        $counts['design'],
                        $counts['prove_print'],
                        $counts['plat_making'],
                        $counts['selesai'],
                        $counts['handed_over'],
                    ],
                    'backgroundColor' => [
                        'rgba(245, 158, 11, 0.72)',
                        'rgba(56, 189, 248, 0.72)',
                        'rgba(129, 140, 248, 0.75)',
                        'rgba(16, 185, 129, 0.75)',
                        'rgba(148, 163, 184, 0.55)',
                    ],
                    'borderRadius' => 6,
                    'borderSkipped' => false,
                    'maxBarThickness' => 24,
                ],
            ],
            'labels' => [
                'Design',
                'Prove',
                'Plate Making',
                'Di Rak',
                'Printing',
            ],
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
                    'border' => ['display' => false],
                    'ticks' => ['precision' => 0, 'stepSize' => 1, 'color' => 'rgba(148, 163, 184, 0.7)'],
                    'grid' => ['color' => 'rgba(148, 163, 184, 0.08)'],
                ],
                'x' => [
                    'border' => ['display' => false],
                    'ticks' => ['color' => 'rgba(148, 163, 184, 0.85)', 'font' => ['size' => 11]],
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
