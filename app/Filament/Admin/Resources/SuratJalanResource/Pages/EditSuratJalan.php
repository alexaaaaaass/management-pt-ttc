<?php

namespace App\Filament\Admin\Resources\SuratJalanResource\Pages;

use App\Filament\Admin\Resources\SuratJalanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSuratJalan extends EditRecord
{
    protected static string $resource = SuratJalanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('preview_pdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-document')
                ->color('warning')
                ->url(fn () => route('suratjalan.preview', $this->record))
                ->openUrlInNewTab(),

            \Filament\Actions\Action::make('download_pdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('suratjalan.download', $this->record)),

            Actions\DeleteAction::make(),
        ];
    }
}
