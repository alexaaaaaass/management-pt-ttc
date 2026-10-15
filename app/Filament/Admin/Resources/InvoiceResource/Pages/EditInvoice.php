<?php

namespace App\Filament\Admin\Resources\InvoiceResource\Pages;

use App\Filament\Admin\Resources\InvoiceResource;
use App\Services\Finance\InvoiceService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(InvoiceService::class)->updateInvoice($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => !app(InvoiceService::class)->canDelete($record)),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $subtotal = (float) ($data['subtotal'] ?? 0);
        $diskon = (float) ($data['diskon'] ?? 0);
        $potongan = (float) ($data['potongan'] ?? 0);
        $ppn = (float) ($data['ppn'] ?? 0);
        $ongkir = (float) ($data['ongkir'] ?? 0);
        $uangMuka = (float) ($data['uang_muka'] ?? 0);

        $diskonNominal = ($subtotal * $diskon) / 100;
        $ppnNominal = ($subtotal * $ppn) / 100;
        $grandTotal = $subtotal - $diskonNominal - $potongan + $ppnNominal + $ongkir;

        $data['grand_total'] = $grandTotal;
        $data['sisa_tagihan'] = $grandTotal - $uangMuka;

        return $data;
    }
}
