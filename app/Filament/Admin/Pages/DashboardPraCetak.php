<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\PracetakPenerimaanResource;
use App\Filament\Admin\Resources\PracetakPlateArchiveResource;
use App\Filament\Admin\Resources\PracetakProcessResource;
use App\Filament\Admin\Widgets\PracetakOverviewWidget;
use App\Filament\Admin\Widgets\PracetakStageChart;
use App\Filament\Admin\Widgets\PracetakStockChart;
use Filament\Actions;
use Filament\Pages\Page;

class DashboardPraCetak extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static ?string $navigationGroup = 'Pra Cetak';

    protected static ?string $navigationLabel = 'Dashboard Pra Cetak';

    protected static ?string $title = 'Dashboard Pra Cetak & Material Plate';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.admin.pages.dashboard-pra-cetak';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_process')
                ->label('Proses Pra Cetak Baru')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(PracetakProcessResource::getUrl('create')),

            Actions\Action::make('penerimaan_plate')
                ->label('Penerimaan Plate')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(PracetakPenerimaanResource::getUrl('index')),

            Actions\Action::make('arsip_plate')
                ->label('Arsip & Rak Plate')
                ->icon('heroicon-o-folder-open')
                ->color('gray')
                ->url(PracetakPlateArchiveResource::getUrl('index')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PracetakOverviewWidget::class,
            PracetakStageChart::class,
            PracetakStockChart::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | string | array
    {
        return 2;
    }
}
