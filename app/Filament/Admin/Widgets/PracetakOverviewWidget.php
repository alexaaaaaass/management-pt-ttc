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
        $totalBaik = (int) ($counts->total_baik ?? 0);
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

        $totalEst = (float) ($counts->total_estimasi ?? 0);
        $totalAct = (float) ($counts->total_aktual ?? 0);
        $variance = $totalEst > 0 ? ($totalEst - $totalAct) : 0.0;

        return [
            Stat::make('Antrean Aktif Pra Cetak', $activeQueue . ' SPK')
                ->description("{$plateMaking} sedang di Tahap C (Plate Making)")
                ->color($activeQueue > 0 ? 'warning' : 'success'),

            Stat::make('Stok Material Plate', number_format($totalSheet, 2, '.', ',') . ' Sheet')
                ->description('Nilai Persediaan: Rp ' . number_format($totalNilaiStok, 0, ',', '.'))
                ->color($totalSheet <= 5 ? 'danger' : 'primary'),

            Stat::make('Arsip Plate Aktif (Rak)', $activeArchives . ' Set')
                ->description("Rusak: {$totalRusak} lbr ({$wasteRate}%) · Biaya Rusak: Rp " . number_format($totalDamageCost, 0, ',', '.') . ' (Klik untuk rekap)')
                ->color($totalRusak > 0 ? 'danger' : 'success')
                ->extraAttributes([
                    'class' => 'cursor-pointer transition hover:ring-2 hover:ring-danger-500/40',
                    'x-on:click' => "\$dispatch('open-damage-recap-modal')",
                ]),

            Stat::make('Variance HPP Plate', ($variance >= 0 ? '+' : '') . 'Rp ' . number_format($variance, 0, ',', '.'))
                ->description('Aktual Rp ' . number_format($totalAct, 0, ',', '.') . ' vs Est. Rp ' . number_format($totalEst, 0, ',', '.'))
                ->color($variance >= 0 ? 'success' : 'danger'),
        ];
    }
}
