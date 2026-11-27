<?php

namespace App\Filament\Admin\Resources\OperasionalPayResource\Pages;

use App\Filament\Admin\Pages\SalesCommissionPage;
use App\Filament\Admin\Resources\OperasionalPayResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOperasionalPays extends ListRecords
{
    protected static string $resource = OperasionalPayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('salesCommission')
                ->label('Rekap Sales Commission')
                ->icon('heroicon-o-currency-dollar')
                ->color('warning')
                ->url(fn () => SalesCommissionPage::getUrl()),

            Actions\CreateAction::make()
                ->label('+ Operasional Keluar'),
        ];
    }
}
