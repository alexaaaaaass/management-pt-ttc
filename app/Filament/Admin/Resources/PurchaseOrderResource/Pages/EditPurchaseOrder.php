<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\Pages;

use App\Filament\Admin\Resources\PurchaseOrderResource;
use Filament\Resources\Pages\EditRecord;
use App\Models\PurchaseRequest;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    public $prItems = [];

 public function mount($record): void
{
    parent::mount($record);

    $items = $this->record->items()->with('item')->get();

    // 🔥 ambil semua data lama dulu
    $data = $this->form->getState();

    // 🔥 inject items TANPA hapus field lain
    $data['items'] = $items->map(function ($item) {
        return [
            'item_id' => $item->item_id,
            'nama_item' => $item->item?->nama_master_item,
            'qty_pr' => $item->qty_pr,
            'qty_po' => $item->qty_po,
            'qty_konversi' => $item->qty_konversi,
            'price' => $item->price,
            'discount' => $item->discount,
            'total' => $item->total,
            'satuan' => $item->satuan,
            'eta' => $item->eta,
            'catatan' => $item->catatan,
        ];
    })->toArray();

    $this->form->fill($data);
}
    protected function getHeaderActions(): array
    {
        $canModify = app(\App\Services\PurchaseOrder\PurchaseOrderService::class)->canModify($this->record);

        return [
            \Filament\Actions\DeleteAction::make()
                ->disabled(! $canModify)
                ->tooltip(! $canModify ? 'Tidak dapat dihapus karena sudah memiliki Penerimaan Barang atau Faktur' : null),
        ];
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        return app(\App\Services\PurchaseOrder\PurchaseOrderService::class)->updatePurchaseOrder($record, $data, $items);
    }
}