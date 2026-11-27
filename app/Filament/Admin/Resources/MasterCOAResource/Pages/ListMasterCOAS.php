<?php

namespace App\Filament\Admin\Resources\MasterCOAResource\Pages;

use App\Filament\Admin\Resources\CoaClassResource;
use App\Filament\Admin\Resources\MasterCOAResource;
use App\Filament\Admin\Resources\MetodeBayarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterCOAS extends ListRecords
{
    protected static string $resource = MasterCOAResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('coaClass')
                ->label('Master COA Class')
                ->icon('heroicon-o-tag')
                ->color('gray')
                ->url(fn (): string => CoaClassResource::getUrl('index')),

            Actions\Action::make('metodeBayar')
                ->label('Metode Pembayaran')
                ->icon('heroicon-o-credit-card')
                ->color('info')
                ->url(fn (): string => MetodeBayarResource::getUrl('index')),

            Actions\Action::make('importExcel')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->url(fn (): string => MasterCOAResource::getUrl('import')),

            Actions\CreateAction::make()
                ->label('+ Tambah Akun COA'),
        ];
    }
}