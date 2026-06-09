<?php

namespace App\Filament\Admin\Resources\CutiResource\Pages;

use App\Filament\Admin\Pages\RekapCutiTahunan;
use App\Filament\Admin\Resources\CutiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCutis extends ListRecords
{
    protected static string $resource = CutiResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\Action::make('cutiTahunan')
    ->label('Cuti Tahunan')
    ->icon('heroicon-o-calendar-days')
    ->color('gray')
   ->url(RekapCutiTahunan::getUrl()),

            Actions\CreateAction::make()
                ->label('Tambah Data Cuti'),

        ];
    }
}