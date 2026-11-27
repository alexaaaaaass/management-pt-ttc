<?php

namespace App\Filament\Admin\Resources\PembPinjamanResource\Pages;

use App\Filament\Admin\Resources\PembPinjamanResource;
use App\Services\Finance\TreasuryService;
use Filament\Resources\Pages\CreateRecord;

class CreatePembPinjaman extends CreateRecord
{
    protected static string $resource = PembPinjamanResource::class;

    protected function afterCreate(): void
    {
        app(TreasuryService::class)->syncPinjamanManualPayment($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
