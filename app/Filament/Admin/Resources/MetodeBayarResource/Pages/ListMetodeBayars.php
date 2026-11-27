<?php

namespace App\Filament\Admin\Resources\MetodeBayarResource\Pages;

use App\Filament\Admin\Resources\MasterCOAResource;
use App\Filament\Admin\Resources\MetodeBayarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMetodeBayars extends ListRecords
{
    protected static string $resource = MetodeBayarResource::class;

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
