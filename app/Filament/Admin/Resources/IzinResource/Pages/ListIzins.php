<?php

namespace App\Filament\Admin\Resources\IzinResource\Pages;

use App\Filament\Admin\Pages\HrdApprovalPage;
use App\Filament\Admin\Resources\CutiResource;
use App\Filament\Admin\Resources\IzinResource;
use App\Filament\Admin\Resources\LemburResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIzins extends ListRecords
{
    protected static string $resource = IzinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backApproval')
                ->label('← Pusat Approval')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => HrdApprovalPage::getUrl()),

            Actions\Action::make('goCuti')
                ->label('Data Cuti')
                ->icon('heroicon-o-calendar')
                ->color('info')
                ->url(fn () => CutiResource::getUrl('index')),

            Actions\Action::make('goLembur')
                ->label('Data Lembur')
                ->icon('heroicon-o-clock')
                ->color('success')
                ->url(fn () => LemburResource::getUrl('index')),

            Actions\CreateAction::make()
                ->label('+ Input Izin / Scorsing'),
        ];
    }
}
