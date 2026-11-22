<?php

namespace App\Filament\Admin\Resources\MaterialStockResource\Pages;

use App\Filament\Admin\Resources\MaterialStockResource;
use App\Filament\Admin\Widgets\StockMinimumAlertWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMaterialStocks extends ListRecords
{
    protected static string $resource = MaterialStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Update IMR') // Ubah tulisan di sini
                ->icon('heroicon-o-plus-circle') // Bisa juga ganti icon
                ->color('success'), // Bisa ganti warna (primary, danger, success, dsb)
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StockMinimumAlertWidget::class,
        ];
    }
}