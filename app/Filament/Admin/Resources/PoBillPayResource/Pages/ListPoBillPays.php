<?php

namespace App\Filament\Admin\Resources\PoBillPayResource\Pages;

use App\Filament\Admin\Resources\PoBillingResource;
use App\Filament\Admin\Resources\PoBillPayResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPoBillPays extends ListRecords
{
    protected static string $resource = PoBillPayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backBilling')
                ->label('← Kembali ke Tagihan PO')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => PoBillingResource::getUrl('index')),

            Actions\CreateAction::make()
                ->label('+ Catat Payment Order'),
        ];
    }
}
