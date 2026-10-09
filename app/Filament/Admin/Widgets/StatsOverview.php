<?php

namespace App\Filament\Admin\Widgets;

use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(
                'Total Sales Order',
                $this->safeCount(SalesOrder::class)
            )
                ->description('Semua sales order')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make(
                'Total Purchase Order',
                $this->safeCount(PurchaseOrder::class)
            )
                ->description('Semua purchase order')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),

            Stat::make(
                'Pengajuan Purchase Request',
                $this->safeCount(PurchaseRequest::class, [
                    'status' => 'deotorisasi'
                ])
            )
                ->description('Menunggu approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(
                'Total Purchase Request',
                $this->safeCount(PurchaseRequest::class)
            )
                ->description('Semua PR')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
        ];
    }

    /**
     * Safe count agar tidak error jika tabel belum ada
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

            return $query->count();

        } catch (\Throwable $e) {
            return 0;
        }
    }
}