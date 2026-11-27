<?php

namespace App\Filament\Admin\Resources\CoaClassResource\Pages;

use App\Filament\Admin\Resources\CoaClassResource;
use App\Filament\Admin\Resources\MasterCOAResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCoaClasses extends ListRecords
{
    protected static string $resource = CoaClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backCoa')
                ->label('← Master Akun (COA)')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => MasterCOAResource::getUrl('index')),

            Actions\CreateAction::make(),
        ];
    }
}
