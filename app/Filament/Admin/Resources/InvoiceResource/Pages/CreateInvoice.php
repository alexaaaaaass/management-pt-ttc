<?php

namespace App\Filament\Admin\Resources\InvoiceResource\Pages;

use App\Filament\Admin\Resources\InvoiceResource;
use App\Models\SuratJalan;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $suratJalan = SuratJalan::with([
            'salesOrder',
            'salesOrder.customer',
        ])->find($data['surat_jalan_id']);

        if ($suratJalan) {

            $data['sales_order_id'] = $suratJalan->sales_order_id;

            $data['customer_id'] = $suratJalan
                ->salesOrder
                ?->customer_id;

            $data['no_po_customer'] = $suratJalan
                ->salesOrder
                ?->no_po_customer;
        }

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