<?php

namespace App\Filament\Admin\Widgets;

use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\SalesOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderOperationsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = true;

    protected int | string | array $columns = [
        'default' => 1,
        'sm' => 2,
        'lg' => 4,
        'xl' => 4,
    ];

    protected function getStats(): array
    {
        $totalSo = $this->safeCount(SalesOrder::class);
        $totalPo = $this->safeCount(PurchaseOrder::class);
        $pendingPr = $this->safeCount(PurchaseRequest::class, ['status' => 'deotorisasi']);
        $totalPr = $this->safeCount(PurchaseRequest::class);

        return [
            Stat::make(
                'Total Sales Order',
                number_format($totalSo, 0, ',', '.')
            )
                ->description('Semua sales order pelanggan')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([20, 28, 35, 42, 55, 68, 80])
                ->color('success'),

            Stat::make(
                'Total Purchase Order',
                number_format($totalPo, 0, ',', '.')
            )
                ->description('Semua purchase order supplier')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->chart([15, 22, 30, 45, 52, 60, 75])
                ->color('primary'),

            Stat::make(
                'Pengajuan Purchase Request',
                number_format($pendingPr, 0, ',', '.')
            )
                ->description($pendingPr > 0 ? 'Menunggu approval / otorisasi' : 'Tidak ada antrean approval')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([40, 35, 30, 25, 20, 15, 10])
                ->color($pendingPr > 0 ? 'warning' : 'gray'),

            Stat::make(
                'Total Purchase Request',
                number_format($totalPr, 0, ',', '.')
            )
                ->description('Semua pengajuan PR')
                ->descriptionIcon('heroicon-m-document-text')
                ->chart([10, 18, 25, 32, 40, 50, 65])
                ->color('info'),
        ];
    }

    /**
     * Safe count agar tidak error jika tabel belum ada atau terjadi kendala query
     */
    private function safeCount(string $model, array $where = []): int
    {
        try {
            if (!class_exists($model)) {
                return 0;
            }

            $query = $model::query();

            foreach ($where as $key => $value) {
                $query->where($key, $value);
            }

            return (int) $query->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
