<?php

namespace App\Filament\Admin\Resources\PenerimaanBarangResource\Pages;

use App\Filament\Admin\Resources\PenerimaanBarangResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePenerimaanBarang extends CreateRecord
{
    protected static string $resource = PenerimaanBarangResource::class;
    protected function afterCreate(): void
{
    foreach ($this->data['items'] as $item) {

        $poItem = \App\Models\PurchaseOrderItem::find($item['purchase_order_item_id']);

        if (!$poItem) continue;

        $qty = (float) $item['qty_terima'];

        // 🔥 cari / buat stock
        $stock = \App\Models\MaterialStock::firstOrCreate(
            ['item_id' => $poItem->item_id],
            ['on_hand' => 0, 'allocation' => 0]
        );

        // 🔥 tambah stock
        $stock->increment('on_hand', $qty);
    }
}
}