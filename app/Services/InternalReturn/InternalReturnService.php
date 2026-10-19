<?php

namespace App\Services\InternalReturn;

use App\Models\InternalReturn;
use App\Models\MaterialStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InternalReturnService
{
    public function approve(InternalReturn $return): void
    {
        DB::transaction(function () use ($return) {
            if ($return->status !== 'draft') {
                return;
            }

            $return->loadMissing('items.imrItem.item');

            foreach ($return->items as $item) {
                if ($item->qty_return <= 0) {
                    continue;
                }

                $imrItem = $item->imrItem;
                if (! $imrItem) {
                    continue;
                }

                // Validasi: Qty return tidak boleh melebihi qty issued - qty returned
                $available = $imrItem->qty_available_return;
                if ($item->qty_return > $available) {
                    $namaItem = $imrItem->item?->nama_master_item ?? 'Material';
                    throw ValidationException::withMessages([
                        'qty_return' => "Qty return untuk {$namaItem} ({$item->qty_return}) melebihi kuantiti yang tersedia untuk direturn ({$available}).",
                    ]);
                }

                // Tambah qty_returned di IMR item
                $imrItem->increment('qty_returned', $item->qty_return);

                // Tambah stock kembali ke gudang (MaterialStock)
                $itemId = $item->item_id ?: $imrItem->item_id;
                if ($itemId) {
                    $stock = MaterialStock::firstOrCreate(
                        ['item_id' => $itemId],
                        ['on_hand' => 0, 'allocation' => 0]
                    );

                    $stock->increment('on_hand', $item->qty_return);
                }
            }

            $return->update([
                'status' => 'approved',
            ]);
        });
    }

    public function cancel(InternalReturn $return): void
    {
        DB::transaction(function () use ($return) {
            if ($return->status !== 'approved') {
                return;
            }

            $return->loadMissing('items.imrItem');

            foreach ($return->items as $item) {
                if ($item->qty_return <= 0) {
                    continue;
                }

                $imrItem = $item->imrItem;
                if ($imrItem) {
                    $imrItem->decrement('qty_returned', $item->qty_return);
                }

                $itemId = $item->item_id ?: $imrItem?->item_id;
                if ($itemId) {
                    $stock = MaterialStock::firstOrCreate(
                        ['item_id' => $itemId],
                        ['on_hand' => 0, 'allocation' => 0]
                    );

                    $stock->decrement('on_hand', $item->qty_return);
                }
            }

            $return->update([
                'status' => 'cancel',
            ]);
        });
    }
}
