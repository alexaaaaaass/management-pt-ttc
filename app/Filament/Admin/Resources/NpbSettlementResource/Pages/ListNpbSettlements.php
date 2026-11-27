<?php

namespace App\Filament\Admin\Resources\NpbSettlementResource\Pages;

use App\Filament\Admin\Resources\NpbRequestResource;
use App\Filament\Admin\Resources\NpbSettlementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNpbSettlements extends ListRecords
{
    protected static string $resource = NpbSettlementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backNpb')
                ->label('← Kembali ke NPB')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => NpbRequestResource::getUrl('index')),

            Actions\CreateAction::make()
                ->label('+ Buat Penyelesaian NPB (LPJ)'),
        ];
    }
}
