<?php

namespace App\Filament\Admin\Widgets;

use App\Models\PracetakProcess;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Schema;

class PracetakStageChart extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Proses Pra Cetak per Tahap';

    protected static bool $isLazy = true;

    protected static ?string $maxHeight = '260px';

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
                    'label' => 'Jumlah Proses SPK',
                    'data' => [
                        $counts['design'],
                        $counts['prove_print'],
                        $counts['plat_making'],
                        $counts['selesai'],
                        $counts['handed_over'],
                    ],
                    'backgroundColor' => [
                        '#f59e0b',
                        '#3b82f6',
                        '#6366f1',
                        '#10b981',
                        '#64748b',
                    ],
                    'borderRadius' => 6,
                    'maxBarThickness' => 42,
                ],
            ],
            'labels' => [
                'A. Trial Design',
                'B. Prove Print',
                'C. Plate Making',
                'D. Tersimpan Rak',
                'E. Di Printing',
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
                    'ticks' => ['precision' => 0],
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
