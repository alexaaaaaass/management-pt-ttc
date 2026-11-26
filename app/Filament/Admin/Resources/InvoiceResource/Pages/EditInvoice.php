<?php

namespace App\Filament\Admin\Resources\InvoiceResource\Pages;

use App\Filament\Admin\Resources\InvoiceResource;
use App\Services\Finance\InvoiceService;
use App\Support\MoneyHelper;
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $grandTotal = (float) ($data['grand_total'] ?? 0);
        $uangMuka = (float) ($data['uang_muka'] ?? 0);
        $paymentsSum = (float) $this->record->payments()->where('status', 'SUCCESS')->sum('nominal_bayar');
        $totalPayment = $uangMuka + $paymentsSum;

        $data['total_payment'] = $totalPayment;
        $data['sisa_tagihan'] = max(0, $grandTotal - $totalPayment);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $subtotal = (float) (MoneyHelper::clean($data['subtotal'] ?? 0) ?? 0);
        $diskon = (float) ($data['diskon'] ?? 0);
        $potongan = (float) (MoneyHelper::clean($data['potongan'] ?? 0) ?? 0);
        $ppn = (float) ($data['ppn'] ?? 0);
        $ongkir = (float) (MoneyHelper::clean($data['ongkir'] ?? 0) ?? 0);
        $uangMuka = (float) (MoneyHelper::clean($data['uang_muka'] ?? 0) ?? 0);

        $paymentsSum = (float) $this->record->payments()->where('status', 'SUCCESS')->sum('nominal_bayar');

        $totals = app(InvoiceService::class)->calculateTotals(
            subtotal: $subtotal,
            diskonPersen: $diskon,
            ppnPersen: $ppn,
            ongkir: $ongkir,
            uangMuka: $uangMuka,
            paymentsSum: $paymentsSum,
            potongan: $potongan
        );

        $data['subtotal'] = $subtotal;
        $data['potongan'] = $potongan;
        $data['ongkir'] = $ongkir;
        $data['uang_muka'] = $uangMuka;
        $data['grand_total'] = $totals['grand_total'];
        $data['total_payment'] = $totals['total_payment'];
        $data['sisa_tagihan'] = $totals['sisa_tagihan'];
        $data['status'] = $totals['status'];

        return $data;
    }
}
