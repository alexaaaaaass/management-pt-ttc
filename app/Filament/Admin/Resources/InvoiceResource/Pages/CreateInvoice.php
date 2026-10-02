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

        return $data;
    }
}