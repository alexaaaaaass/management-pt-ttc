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
                SalesOrder::count()
            )
                ->description('Semua sales order')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make(
                'Total Purchase Order',
                PurchaseOrder::count()
            )
                ->description('Semua purchase order')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),

            Stat::make(
                'Pengajuan Purchase Req',
                PurchaseRequest::where(
                    'status',
                    'deotorisasi'
                )->count()
            )
                ->description('Menunggu approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(
                'Total Purchase Request',
                PurchaseRequest::count()
            )
                ->description('Semua PR')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

        ];
    }
}