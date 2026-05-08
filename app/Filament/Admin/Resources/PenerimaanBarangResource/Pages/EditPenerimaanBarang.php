<?php

namespace App\Filament\Admin\Resources\PenerimaanBarangResource\Pages;

use App\Filament\Admin\Resources\PenerimaanBarangResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\PurchaseOrder;

class EditPenerimaanBarang extends EditRecord
{
    protected static string $resource = PenerimaanBarangResource::class;
   public function mount($record): void
{
    parent::mount($record);

    // 🔥 ambil dari penerimaan (bukan PO)
    $items = $this->record->items()
        ->with(['purchaseOrderItem.item.satuan'])
        ->get();

    // 🔥 kalau BELUM ADA (misal data lama)
    if ($items->isEmpty() && $this->record->purchase_order_id) {

        $po = PurchaseOrder::with([
            'items.item.satuan',
        ])->find($this->record->purchase_order_id);

        if (!$po) return;

       $items = $items->map(function ($item) {
    return [
        'purchase_order_item_id' => $item->purchase_order_item_id,

        'nama_item_display' =>
            ($item->purchaseOrderItem->item->kode_material ?? '-') . ' - ' .
            ($item->purchaseOrderItem->item->nama_master_item ?? '-'),

        'qty_po_display' =>
            number_format($item->purchaseOrderItem->qty_po ?? 0, 2) . ' | ' .
            ($item->purchaseOrderItem->item->satuan->nama_satuan ?? '-'),

        'qty_sebelumnya_display' =>
            number_format($item->qty_terima ?? 0, 2) . ' | ' .
            ($item->purchaseOrderItem->item->satuan->nama_satuan ?? '-'),

        'qty_terima' => $item->qty_terima,
        'catatan_item' => $item->catatan_item,
        'tgl_exp' => $item->tgl_exp,
        'no_lot' => $item->no_lot,

        'catatan_po_display' => $item->purchaseOrderItem->catatan ?? '-',
    ];
});

    } else {

        // 🔥 mapping dari DB (INI YANG BENAR)
        $items = $items->map(function ($item) {
            return [
                'purchase_order_item_id' => $item->purchase_order_item_id,

                'nama_item_display' =>
                    ($item->purchaseOrderItem->item->kode_material ?? '-') . ' - ' .
                    ($item->purchaseOrderItem->item->nama_master_item ?? '-'),

                'qty_po_display' =>
                    number_format($item->purchaseOrderItem->qty_po ?? 0, 2) . ' | ' .
                    ($item->purchaseOrderItem->item->satuan->nama_satuan ?? '-'),

                'qty_sebelumnya_display' =>
                    number_format($item->qty_terima ?? 0, 2) . ' | ' .
                    ($item->purchaseOrderItem->item->satuan->nama_satuan ?? '-'),

                'qty_terima' => $item->qty_terima,
                'catatan_item' => $item->catatan_item,
                'tgl_exp' => $item->tgl_exp,
                'no_lot' => $item->no_lot,

                'catatan_po_display' => $item->purchaseOrderItem->catatan ?? '-',
            ];
        });
    }

   $this->form->fill([
    'purchase_order_id' => $this->record->purchase_order_id,
    'tanggal_terima' => $this->record->tanggal_terima,
    'no_surat_jalan' => $this->record->no_surat_jalan,
    'nama_pengirim' => $this->record->nama_pengirim,
    'nopol_kendaraan' => $this->record->nopol_kendaraan,
    'catatan_pengiriman' => $this->record->catatan_pengiriman,

    'items' => $items->toArray(),
]);
}
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
{
    // 🔥 rollback stock lama
    foreach ($this->record->items as $oldItem) {

        $poItem = \App\Models\PurchaseOrderItem::find($oldItem->purchase_order_item_id);

        if (!$poItem) continue;

        $stock = \App\Models\MaterialStock::where('item_id', $poItem->item_id)->first();

        if ($stock) {
            $stock->decrement('on_hand', $oldItem->qty_terima);
        }
    }

    // 🔥 hapus item lama
    $this->record->items()->delete();
}
}