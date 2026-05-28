<?php

namespace App\Filament\Admin\Resources\TransKasBankResource\Pages;

use App\Filament\Admin\Resources\TransKasBankResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransKasBank extends CreateRecord
{
    protected static string $resource =
        TransKasBankResource::class;

    protected function mutateFormDataBeforeCreate(
        array $data
    ): array {

        $data['tipe_transaksi'] =
            $data['tipe_transaksi']
            ?? request()->get('type')
            ?? 'BANK_MASUK';
        // dd($data);
        return $data;
    }
}