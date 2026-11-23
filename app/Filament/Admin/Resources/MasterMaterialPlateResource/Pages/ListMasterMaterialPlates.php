<?php

namespace App\Filament\Admin\Resources\MasterMaterialPlateResource\Pages;

use App\Filament\Admin\Resources\MasterMaterialPlateResource;
use App\Filament\Admin\Resources\PracetakMaterialMovementResource;
use App\Filament\Admin\Resources\PracetakPenerimaanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterMaterialPlates extends ListRecords
{
    protected static string $resource = MasterMaterialPlateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('histori_ledger')
                ->label('Buku Besar Mutasi (Ledger)')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->url(PracetakMaterialMovementResource::getUrl('index')),

            Actions\Action::make('penerimaan_baru')
                ->label('Saldo Awal / Terima Manual')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->tooltip('Untuk input saldo awal / terima tanpa PO. Pembelian rutin gunakan alur PR → PO → Penerimaan Barang (LPB).')
                ->url(PracetakPenerimaanResource::getUrl('index')),

            Actions\CreateAction::make()
                ->label('Tambah Material Plate'),
        ];
    }
}
