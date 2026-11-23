<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\Pages;

use App\Filament\Admin\Resources\PurchaseOrderResource;
use App\Services\PurchaseOrder\PurchaseOrderService;
use App\Support\MoneyHelper;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    public $prItems = [];

    public function mount($record): void
    {
        parent::mount($record);

        $items = $this->record->items()->with('item')->get();

        // Ambil semua data lama dulu
        $data = $this->form->getState();

        // Hydrate pr_preview agar Tahap 1 tetap informatif saat Edit
        $data['pr_preview'] = PurchaseOrderResource::buildPrPreviewHtml(
            $this->record->purchase_request_id ? (int) $this->record->purchase_request_id : null,
            (int) $this->record->id,
            false
        );

        // Inject items lengkap dengan id agar updatePurchaseOrder menjaga riwayat qty_received
        $data['items'] = $items->map(function ($item) {
            return [
                'id'           => $item->id,
                'item_id'      => $item->item_id,
                'nama_item'    => $item->item?->nama_master_item,
                'qty_pr'       => $item->qty_pr,
                'qty_sisa_pr'  => $item->qty_pr,
                'qty_po'       => $item->qty_po,
                'qty_konversi' => $item->qty_konversi,
                'price'        => MoneyHelper::format($item->price, 2) ?? '0',
                'discount'     => $item->discount,
                'total'        => MoneyHelper::format($item->total, 2) ?? '0',
                'satuan'       => $item->satuan,
                'eta'          => $item->eta,
                'catatan'      => $item->catatan,
            ];
        })->toArray();

        $this->form->fill($data);
    }

    protected function getHeaderActions(): array
    {
        $canModify = app(PurchaseOrderService::class)->canModify($this->record);

        return [
            \Filament\Actions\DeleteAction::make()
                ->disabled(! $canModify)
                ->tooltip(! $canModify ? 'Tidak dapat dihapus karena sudah memiliki Penerimaan Barang, Tagihan Supplier, atau Faktur' : null)
                ->using(fn ($record) => app(PurchaseOrderService::class)->deletePurchaseOrder($record)),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $items = $data['items'] ?? [];
        unset($data['items'], $data['pr_preview']);

        try {
            return app(PurchaseOrderService::class)->updatePurchaseOrder($record, $data, $items);
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->implode(' | ');

            Notification::make()
                ->title('Gagal Memperbarui Purchase Order')
                ->body($msg)
                ->danger()
                ->send();

            $this->halt();
            throw $e;
        }
    }
}
