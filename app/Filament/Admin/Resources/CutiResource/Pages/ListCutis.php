<?php

namespace App\Filament\Admin\Resources\CutiResource\Pages;

use App\Filament\Admin\Pages\HrdApprovalPage;
use App\Filament\Admin\Pages\RekapCutiTahunan;
use App\Filament\Admin\Resources\CutiResource;
use App\Filament\Admin\Resources\IzinResource;
use App\Filament\Admin\Resources\LemburResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCutis extends ListRecords
{
    protected static string $resource = CutiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backApproval')
                ->label('← Pusat Approval')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => HrdApprovalPage::getUrl()),

            Actions\Action::make('goIzin')
                ->label('Izin & Scorsing')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->url(fn () => IzinResource::getUrl('index')),

            Actions\Action::make('goLembur')
                ->label('Data Lembur')
                ->icon('heroicon-o-clock')
                ->color('success')
                ->url(fn () => LemburResource::getUrl('index')),

            Actions\Action::make('cutiTahunan')
                ->label('Rekap Cuti Tahunan')
                ->icon('heroicon-o-calendar-days')
                ->color('info')
                ->url(fn () => RekapCutiTahunan::getUrl()),

            Actions\CreateAction::make()
                ->label('+ Tambah Cuti'),
        ];
    }
}