<?php

namespace App\Filament\Admin\Resources\NpbRequestResource\Pages;

use App\Filament\Admin\Resources\NpbRequestResource;
use App\Filament\Admin\Resources\NpbSettlementResource;
use App\Services\Finance\NpbService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNpbRequest extends EditRecord
{
    protected static string $resource = NpbRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('previewPdf')
                ->label('Preview PDF NPB')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn () => route('finance.npb.preview', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('downloadPdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('finance.npb.download', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('createLpj')
                ->label('Buat Penyelesaian (LPJ)')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('warning')
                ->visible(fn () => $this->record->is_approved_status)
                ->url(fn () => NpbSettlementResource::getUrl('create', ['npb_request_id' => $this->record->id])),

            Actions\DeleteAction::make()
                ->using(fn () => app(NpbService::class)->deleteRequestWithLedgerCleanup($this->record)),
        ];
    }

    protected function afterSave(): void
    {
        app(NpbService::class)->recalculateRequest($this->record);
    }
}
