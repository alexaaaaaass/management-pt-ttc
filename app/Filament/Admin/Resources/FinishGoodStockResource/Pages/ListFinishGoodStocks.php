<?php

namespace App\Filament\Admin\Resources\FinishGoodStockResource\Pages;

use App\Filament\Admin\Resources\FinishGoodStockResource;
use App\Services\Inventory\FinishGoodStockService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListFinishGoodStocks extends ListRecords
{
    protected static string $resource = FinishGoodStockResource::class;

    public function mount(): void
    {
        parent::mount();

        // Pastikan seluruh FinishGoodItem sudah terdaftar di tabel FinishGoodStock
        app(FinishGoodStockService::class)->syncExistingStock();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync_stock')
                ->label('Sinkronkan Stok Produksi')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action(function () {
                    $count = app(FinishGoodStockService::class)->syncExistingStock();
                    Notification::make()
                        ->title('Sinkronisasi Stok Selesai')
                        ->body("{$count} item barang jadi telah disinkronkan dengan riwayat Packaging, Subcount In, dan Surat Jalan.")
                        ->success()
                        ->send();
                }),
        ];
    }
}

