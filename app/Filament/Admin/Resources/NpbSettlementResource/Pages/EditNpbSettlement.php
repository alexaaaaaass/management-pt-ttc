<?php

namespace App\Filament\Admin\Resources\NpbSettlementResource\Pages;

use App\Filament\Admin\Resources\NpbSettlementResource;
use App\Services\Finance\NpbService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNpbSettlement extends EditRecord
{
    protected static string $resource = NpbSettlementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('previewPdf')
                ->label('Preview PDF LPJ')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn () => route('finance.npb-settlement.preview', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('downloadPdf')
                ->label('Download PDF LPJ')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('finance.npb-settlement.download', $this->record))
                ->openUrlInNewTab(),

            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        app(NpbService::class)->recalculateSettlement($this->record);
    }
}
