<?php

namespace App\Filament\Admin\Resources\PracetakPenerimaanResource\Pages;

use App\Filament\Admin\Resources\MasterMaterialPlateResource;
use App\Filament\Admin\Resources\PracetakPenerimaanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPracetakPenerimaans extends ListRecords
{
    protected static string $resource = PracetakPenerimaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('lihat_stok')
                ->label('Lihat Material & Stok')
                ->icon('heroicon-o-square-3-stack-3d')
                ->color('gray')
                ->url(MasterMaterialPlateResource::getUrl('index')),

            Actions\CreateAction::make()->label('Penerimaan Baru'),
        ];
    }
}
