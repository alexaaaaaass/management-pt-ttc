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
        ->with(['purchaseOrderItem.item.satuan', 'purchaseOrderItem.item.materialPlate'])
        ->get();

    // 🔥 kalau BELUM ADA (misal data lama)
    if ($items->isEmpty() && $this->record->purchase_order_id) {

        $po = PurchaseOrder::with([
            'items.item.satuan',
            'items.item.materialPlate',
        ])->find($this->record->purchase_order_id);

        if (!$po) return;

        $items = $po->items->map(function ($poItem) {
            $routeTag = $poItem->item?->materialPlate ? ' [STOK PLATE PREPRESS]' : '';

            return [
                'purchase_order_item_id' => $poItem->id,
                'nama_item_display'      => ($poItem->item?->kode_master_item ?? '-') . ' - ' . ($poItem->item?->nama_master_item ?? '-') . $routeTag,
                'qty_po_display'         => number_format((float) ($poItem->qty_po ?? 0), 2) . ' | ' . ($poItem->item?->satuan?->nama_satuan ?? '-'),
                'qty_sebelumnya_display' => number_format((float) ($poItem->qty_received ?? 0), 2) . ' | ' . ($poItem->item?->satuan?->nama_satuan ?? '-'),
                'qty_terima'             => 0,
                'catatan_item'           => null,
                'tgl_exp'                => null,
                'no_lot'                 => null,
                'catatan_po_display'     => $poItem->catatan ?? '-',
            ];
        });

    } else {

        // 🔥 mapping dari DB (INI YANG BENAR)
        $items = $items->map(function ($item) {
            $routeTag = $item->purchaseOrderItem?->item?->materialPlate ? ' [STOK PLATE PREPRESS]' : '';

            return [
                'purchase_order_item_id' => $item->purchase_order_item_id,

                'nama_item_display' =>
                    ($item->purchaseOrderItem?->item?->kode_master_item ?? '-') . ' - ' .
                    ($item->purchaseOrderItem?->item?->nama_master_item ?? '-') . $routeTag,

                'qty_po_display' =>
                    number_format($item->purchaseOrderItem?->qty_po ?? 0, 2) . ' | ' .
                    ($item->purchaseOrderItem?->item?->satuan?->nama_satuan ?? '-'),

                'qty_sebelumnya_display' =>
                    number_format($item->qty_terima ?? 0, 2) . ' | ' .
                    ($item->purchaseOrderItem?->item?->satuan?->nama_satuan ?? '-'),

                'qty_terima' => $item->qty_terima,
                'catatan_item' => $item->catatan_item,
                'tgl_exp' => $item->tgl_exp,
                'no_lot' => $item->no_lot,

                'catatan_po_display' => $item->purchaseOrderItem?->catatan ?? '-',
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
        $canModify = app(\App\Services\PurchaseOrder\PenerimaanBarangService::class)->canModify($this->record);

        return [
            Actions\DeleteAction::make()
                ->disabled(! $canModify)
                ->tooltip(! $canModify ? 'Tidak dapat dihapus karena sudah memiliki Retur Eksternal terkait.' : null),
        ];
    }

    protected function beforeSave(): void
    {
        $this->record->loadMissing(['items.purchaseOrderItem', 'purchaseOrder']);

        // Rollback stock lama (auto-route: Material Umum / Material Plate Prepress) & decrement qty_received PO
        foreach ($this->record->items as $oldItem) {
            $poItem = $oldItem->purchaseOrderItem;

            if (! $poItem) {
                continue;
            }

            if ((float) $oldItem->qty_terima > 0) {
                $this->record->applyStockMovement(
                    poItem: $poItem,
                    qty: (float) $oldItem->qty_terima,
                    isIncrease: false,
                    remarks: 'Rollback Penerimaan Barang Edit - ' . ($this->record->no_penerimaan ?: $this->record->no_surat_jalan)
                );

                $poItem->decrement('qty_received', $oldItem->qty_terima);
            }
        }

        // Hapus item lama
        $this->record->items()->delete();
    }

    protected function afterSave(): void
    {
        $this->record->processReceiving();
    }
}