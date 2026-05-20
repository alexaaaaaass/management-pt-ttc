<?php

namespace App\Filament\Admin\Resources\InvoiceResource\Pages;

use App\Filament\Admin\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $subtotal = (float) ($data['subtotal'] ?? 0);

        $diskon = (float) ($data['diskon'] ?? 0);

        $ppn = (float) ($data['ppn'] ?? 0);

        $ongkir = (float) ($data['ongkir'] ?? 0);

        $uangMuka = (float) ($data['uang_muka'] ?? 0);

        /*
        |--------------------------------------------------------------------------
        | HITUNG
        |--------------------------------------------------------------------------
        */

        $diskonNominal =
            ($subtotal * $diskon) / 100;

        $ppnNominal =
            ($subtotal * $ppn) / 100;

        $grandTotal =
            $subtotal
            - $diskonNominal
            + $ppnNominal
            + $ongkir;

        /*
        |--------------------------------------------------------------------------
        | SAVE
        |--------------------------------------------------------------------------
        */

        $data['grand_total'] = $grandTotal;

        $data['sisa_tagihan'] =
            $grandTotal - $uangMuka;

        return $data;
    }
}