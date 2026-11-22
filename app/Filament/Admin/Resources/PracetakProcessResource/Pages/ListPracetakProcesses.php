<?php

namespace App\Filament\Admin\Resources\PracetakProcessResource\Pages;

use App\Filament\Admin\Resources\PracetakProcessResource;
use App\Filament\Admin\Widgets\PracetakOverviewWidget;
use App\Filament\Admin\Widgets\PracetakStageChart;
use App\Filament\Admin\Widgets\PracetakStockChart;
use App\Models\PracetakProcess;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPracetakProcesses extends ListRecords
{
    protected static string $resource = PracetakProcessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Proses Pra Cetak'),
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

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(PracetakProcess::count()),

            'design' => Tab::make('A. Trial Design')
                ->badge(PracetakProcess::whereIn('status', ['design', 'trial_design'])->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['design', 'trial_design'])),

            'prove' => Tab::make('B. Prove Print')
                ->badge(PracetakProcess::where('status', 'prove_print')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'prove_print')),

            'plat' => Tab::make('C. Plate Making')
                ->badge(PracetakProcess::where('status', 'plat_making')->count())
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'plat_making')),

            'selesai' => Tab::make('D. Tersimpan di Rak')
                ->badge(PracetakProcess::where('status', 'selesai')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'selesai')),

            'handed_over' => Tab::make('Diserahkan Printing')
                ->badge(PracetakProcess::where('status', 'handed_over')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'handed_over')),
        ];
    }
}
