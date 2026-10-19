<?php

namespace App\Filament\Admin\Resources\ApprovalFormResource\Pages;

use App\Filament\Admin\Resources\ApprovalFormResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditApprovalForm extends EditRecord
{
    protected static string $resource = ApprovalFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview_pdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn () => route('approval-form.preview', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('download_pdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->url(fn () => route('approval-form.download', $this->record)),

            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
