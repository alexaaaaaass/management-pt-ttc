<?php

namespace App\Filament\Admin\Resources\PoBillingResource\Pages;

use App\Filament\Admin\Resources\PoBillingResource;
use App\Filament\Admin\Resources\PoBillPayResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPoBillings extends ListRecords
{
    protected static string $resource = PoBillingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('allPayments')
                ->label('Semua Payment Order')
                ->icon('heroicon-o-banknotes')
                ->color('info')
                ->url(fn () => PoBillPayResource::getUrl('index')),

            Actions\CreateAction::make()
                ->label('+ Buat Tagihan PO'),
        ];
    }
}
