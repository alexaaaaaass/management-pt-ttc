<?php

namespace App\Filament\Admin\Resources\FinishGoodItemResource\Pages;

use App\Filament\Admin\Resources\FinishGoodItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFinishGoodItems extends ListRecords
{
    protected static string $resource = FinishGoodItemResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\Action::make('calculator')
                ->label('Kalkulator Etiket')
                ->icon('heroicon-o-calculator')
                ->color('success')
                ->url(
                    FinishGoodItemResource::getUrl('calculator')
                ),

            Actions\CreateAction::make(),

        ];
    }
}