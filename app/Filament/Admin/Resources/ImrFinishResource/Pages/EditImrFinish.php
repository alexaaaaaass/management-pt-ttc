<?php

namespace App\Filament\Admin\Resources\ImrFinishResource\Pages;

use App\Filament\Admin\Resources\ImrFinishResource;
use App\Models\ImrFinish;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditImrFinish extends EditRecord
{
    protected static string $resource = ImrFinishResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var ImrFinish $record */
        $record = $this->getRecord();
        $record->loadMissing(['items.item.satuan']);

        $data['imr_items'] = $record->items->map(function ($item) {
            return [
                'item_id'       => $item->item_id,
                'nama_item'     => $item->item?->nama_master_item,
                'satuan'        => $item->satuan ?: optional($item->item?->satuan)->nama_satuan,
                'total_pesanan' => (float) $item->total_pesanan,
                'qty_request'   => (float) $item->qty_request,
                'qty_input'     => (float) $item->qty_request,
                'qty_approved'  => (float) $item->qty_approved,
                'catatan'       => $item->catatan,
            ];
        })->toArray();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $items = $data['imr_items'] ?? [];
        unset($data['imr_items']);

        return DB::transaction(function () use ($record, $data, $items) {
            $record->update($data);

            if ($record->status === 'Pending') {
                $record->items()->delete();
                foreach ($items as $item) {
                    $qtyReq = (float) ($item['qty_request'] ?? ($item['qty_input'] ?? 0));
                    if ($qtyReq <= 0 || empty($item['item_id'])) {
                        continue;
                    }

                    $record->items()->create([
                        'item_id'       => $item['item_id'],
                        'satuan'        => $item['satuan'] ?? '',
                        'total_pesanan' => (float) ($item['total_pesanan'] ?? 0),
                        'qty_request'   => $qtyReq,
                        'qty_approved'  => 0,
                        'qty_issued'    => 0,
                        'qty_returned'  => 0,
                        'catatan'       => $item['catatan'] ?? null,
                    ]);
                }
            }

            return $record;
        });
    }
}
