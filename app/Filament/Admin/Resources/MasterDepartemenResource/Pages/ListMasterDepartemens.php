<?php

namespace App\Filament\Admin\Resources\MasterDepartemenResource\Pages;

use App\Filament\Admin\Resources\KaryawanResource;
use App\Filament\Admin\Resources\MasterDepartemenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterDepartemens extends ListRecords
{
    protected static string $resource = MasterDepartemenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backKaryawan')
                ->label('← Karyawan & Master')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => KaryawanResource::getUrl('index')),

            Actions\CreateAction::make()
                ->label('+ Tambah Departemen'),
        ];
    }
}
