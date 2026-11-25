<?php

namespace App\Filament\Admin\Resources\IMRDieMakingResource\Pages;

use App\Filament\Admin\Resources\IMRDieMakingResource;
use App\Models\IMRPrinting;
use App\Models\MasterItem;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EditIMRDieMaking extends EditRecord
{
    protected static string $resource = IMRDieMakingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => in_array($this->record->status, ['Pending', 'Rejected'], true)),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var IMRPrinting $record */
        $record = $this->record;
        $record->loadMissing(['items.item.satuan', 'items.item.stock']);

        $data['imr_items'] = $record->items->map(function ($item): array {
            $satuan = $item->satuan ?: ($item->item?->satuan?->nama_satuan ?? '-');
            $available = (float) ($item->item?->stock?->available ?? 0);

            return [
                'item_id'       => $item->item_id,
                'nama_item'     => $item->item?->nama_master_item ?? '-',
                'department'    => (float) $item->total_pesanan > 0 ? 'BOM SPK' : 'Tambahan Manual',
                'satuan'        => $satuan,
                'stok_gudang'   => number_format($available, 2, ',', '.') . ' ' . $satuan,
                'total_pesanan' => (float) $item->total_pesanan,
                'qty_approved'  => (float) $item->qty_approved,
                'qty_request'   => (float) $item->qty_request,
                'qty_input'     => (float) $item->qty_request,
                'catatan'       => $item->catatan,
            ];
        })->all();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var IMRPrinting $record */
        if ($record->status !== 'Pending') {
            throw ValidationException::withMessages([
                'status' => 'Hanya IMR dengan status Pending yang dapat diubah.',
            ]);
        }

        $items = $data['imr_items'] ?? [];
        unset($data['imr_items'], $data['no_imr']);

        $validItems = [];
        foreach ($items as $item) {
            $itemId = (int) ($item['item_id'] ?? 0);
            $qtyReq = max(0, (float) ($item['qty_input'] ?? $item['qty_request'] ?? 0));
            if ($itemId > 0 && $qtyReq > 0) {
                $item['item_id'] = $itemId;
                $item['qty_request'] = $qtyReq;
                $validItems[] = $item;
            }
        }

        if (empty($validItems)) {
            throw ValidationException::withMessages([
                'imr_items' => 'Minimal isi Qty Request lebih dari 0 pada 1 material.',
            ]);
        }

        return DB::transaction(function () use ($record, $data, $validItems) {
            $record->update([
                'spk_id'          => $data['spk_id'],
                'departemen_id'   => $data['departemen_id'],
                'tanggal_request' => $data['tanggal_request'],
            ]);

            $record->items()->delete();

            $itemIds = array_unique(array_column($validItems, 'item_id'));
            $masterItems = MasterItem::with('satuan')->whereIn('id', $itemIds)->get()->keyBy('id');

            foreach ($validItems as $item) {
                $masterItem = $masterItems->get($item['item_id']);
                $satuan = trim((string) ($item['satuan'] ?? ''))
                    ?: ($masterItem?->satuan?->nama_satuan ?? '-');

                $record->items()->create([
                    'item_id'       => $item['item_id'],
                    'satuan'        => $satuan,
                    'total_pesanan' => (float) ($item['total_pesanan'] ?? 0),
                    'qty_request'   => $item['qty_request'],
                    'qty_approved'  => 0,
                    'qty_issued'    => 0,
                    'qty_returned'  => 0,
                    'catatan'       => $item['catatan'] ?? null,
                ]);
            }

            return $record->fresh(['items']);
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
