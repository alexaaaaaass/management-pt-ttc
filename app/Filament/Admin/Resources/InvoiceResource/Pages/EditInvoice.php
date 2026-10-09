<?php

namespace App\Filament\Admin\Resources\InvoiceResource\Pages;

use App\Filament\Admin\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview_pdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-document')
                ->color('warning')
                ->url(fn () => route('invoice.preview', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('download_pdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('invoice.download', $this->record)),

            Actions\DeleteAction::make(),
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
