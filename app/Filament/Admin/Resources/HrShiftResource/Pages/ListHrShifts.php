<?php

namespace App\Filament\Admin\Resources\HrShiftResource\Pages;

use App\Filament\Admin\Resources\HrShiftResource;
use App\Filament\Admin\Resources\KaryawanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHrShifts extends ListRecords
{
    protected static string $resource = HrShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backKaryawan')
                ->label('← Karyawan & Master')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => KaryawanResource::getUrl('index')),

            Actions\CreateAction::make()->label('+ Tambah Shift'),
        ];
    }
}
