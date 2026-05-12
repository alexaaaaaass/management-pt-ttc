<?php

namespace App\Filament\Admin\Resources\IMRPrintingResource\Pages;

use App\Filament\Admin\Resources\IMRPrintingResource;
use App\Models\IMRPrinting;
// use App\Models\MaterialStock;
use Filament\Resources\Pages\CreateRecord;

class CreateIMRPrinting extends CreateRecord
{
    protected static string $resource = IMRPrintingResource::class;
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $items = $data['imr_items'] ?? [];
        unset($data['imr_items']);

        $imr = IMRPrinting::create($data);

        foreach ($items as $item) {

    $imr->items()->create([
        'item_id'        => $item['item_id'] ?? null,
        'department'     => $item['department'] ?? '-',
        'satuan'         => $item['satuan'] ?? '-',
        'total_pesanan'  => $item['total_pesanan'] ?? 0,
        'qty_request'    => $item['qty_request'] ?? 0,
        'qty_input'      => $item['qty_input'] ?? 0,
        'qty_approved'   => $item['qty_approved'] ?? 0,
        'catatan'        => '-',
        'status'         => 'pending',
    ]);

}

        return $imr;
    }
}