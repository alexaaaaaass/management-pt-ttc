<?php

namespace App\Filament\Admin\Resources\PayrollBoronganResource\Pages;

use App\Filament\Admin\Resources\PayrollBoronganResource;
use App\Services\HR\PayrollService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayrollBorongan extends EditRecord
{
    protected static string $resource = PayrollBoronganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('previewPdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn () => route('hrd.borongan.preview', $this->record))
                ->openUrlInNewTab(),

            Actions\DeleteAction::make()
                ->using(fn () => app(PayrollService::class)->deleteBorongan($this->record)),
        ];
    }

    protected function afterSave(): void
    {
        app(PayrollService::class)->recalculateBorongan($this->record);
    }
}
