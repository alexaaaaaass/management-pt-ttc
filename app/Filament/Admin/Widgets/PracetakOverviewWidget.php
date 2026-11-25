<?php

namespace App\Filament\Admin\Widgets;

use App\Models\PracetakMaterialStock;
use App\Models\PracetakPlateArchive;
use App\Models\PracetakProcess;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PracetakOverviewWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected static ?string $pollingInterval = null;

    protected int | string | array $columnSpan = 'full';

    protected int | string | array $columns = [
        'default' => 1,
        'sm' => 2,
        'lg' => 4,
        'xl' => 4,
    ];

    protected function getStats(): array
    {
        $counts = PracetakProcess::query()
            ->selectRaw("
                SUM(CASE WHEN status IN ('design', 'trial_design', 'prove_print', 'plat_making') THEN 1 ELSE 0 END) as active_queue,
                SUM(CASE WHEN status = 'plat_making' THEN 1 ELSE 0 END) as plate_making_count,
                COALESCE(SUM(hasil_baik), 0) as total_baik,
                COALESCE(SUM(hasil_rusak), 0) as total_rusak,
                COALESCE(SUM(estimasi_biaya_plate), 0) as total_estimasi,
                COALESCE(SUM(actual_total_cost), 0) as total_aktual
            ")
            ->first();

        $activeQueue = (int) ($counts->active_queue ?? 0);
        $plateMaking = (int) ($counts->plate_making_count ?? 0);
        $damageOverall = app(\App\Services\Pracetak\PracetakCostingService::class)->calculateOverallDamageSummary();
        $totalRusak = (int) $damageOverall['total_rusak'];
        $wasteRate = (float) $damageOverall['waste_rate'];
        $totalDamageCost = (float) $damageOverall['total_damage_cost'];

        $activeArchives = PracetakPlateArchive::whereIn('status_kondisi', ['tersimpan', 'dipakai_printing'])->count();

        $stockAgg = PracetakMaterialStock::query()
            ->selectRaw('COALESCE(SUM(stok_sheet_utuh), 0) as total_sheet, COALESCE(SUM(total_nilai_stok), 0) as total_nilai')
            ->first();

        $totalSheet = (float) ($stockAgg->total_sheet ?? 0);
        $totalNilaiStok = (float) ($stockAgg->total_nilai ?? 0);

        return [
            Stat::make('Antrean Aktif', $activeQueue . ' SPK')
                ->description("{$plateMaking} di tahap Plate Making")
                ->descriptionIcon('heroicon-m-clock')
                ->color($activeQueue > 0 ? 'warning' : 'gray'),

            Stat::make('Stok Plate', number_format($totalSheet, 2, ',', '.') . ' Sheet')
                ->description('Nilai Rp ' . number_format($totalNilaiStok, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-cube')
                ->color($totalSheet <= 5 ? 'danger' : 'gray'),

            Stat::make('Arsip di Rak', $activeArchives . ' Set')
                ->description('Siap cetak & repeat order')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('success'),

            Stat::make('Biaya Kerusakan Plate', 'Rp ' . number_format($totalDamageCost, 0, ',', '.'))
                ->description("{$totalRusak} lbr rusak (" . number_format($wasteRate, 1, ',', '.') . '%) · Rincian')
                ->descriptionIcon('heroicon-m-arrow-up-right')
                ->color($totalRusak > 0 ? 'danger' : 'success')
                ->extraAttributes([
                    'class' => 'cursor-pointer transition hover:opacity-90',
                    'x-on:click' => "\$dispatch('open-damage-recap-modal')",
                ]),
        ];
    }
}
