<?php

namespace App\Filament\Admin\Resources\PembPinjamanResource\Pages;

use App\Filament\Admin\Resources\PembPinjamanResource;
use App\Services\Finance\TreasuryService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPembPinjaman extends EditRecord
{
    protected static string $resource = PembPinjamanResource::class;

    protected function afterSave(): void
    {
        app(TreasuryService::class)->syncPinjamanManualPayment($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(fn () => app(TreasuryService::class)->deletePinjamanManualPayment($this->record)),
        ];
    }
}
