<?php

namespace App\Filament\Admin\Resources\TransKasBankResource\Pages;

use App\Filament\Admin\Resources\TransKasBankResource;
use App\Services\Finance\TreasuryService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTransKasBank extends CreateRecord
{
    protected static string $resource = TransKasBankResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tipe_transaksi'] = $data['tipe_transaksi']
            ?? request()->get('type')
            ?? 'BANK_MASUK';

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(TreasuryService::class)->recordBank($data);
    }
}