<?php

namespace App\Services\ExternalReturn;

use App\Models\ExternalReturn;
use App\Models\MaterialStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExternalReturnService
{
   

    public function approve(ExternalReturn $return): void
{
    DB::transaction(function () use ($return) {

        // Hanya draft yang boleh diapprove
        if ($return->status !== 'draft') {
            return;
        }

        $return->loadMissing(
            'items.penerimaanBarangItem.purchaseOrderItem'
        );

        foreach ($return->items as $item) {

            if ($item->qty_return <= 0) {
                continue;
            }

            $pbItem = $item->penerimaanBarangItem;

            if (!$pbItem || !$pbItem->purchaseOrderItem) {
                continue;
            }

            $poItem = $pbItem->purchaseOrderItem;
            $plate = \App\Models\MasterMaterialPlate::where('master_item_id', $poItem->item_id)->first();

            if ($plate) {
                app(\App\Services\Pracetak\PracetakStockService::class)->rollbackFromLpb(
                    plate: $plate,
                    reference: $return,
                    qty: (float) $item->qty_return,
                    unitPrice: (float) ($poItem->price ?? 0),
                    supplierId: $poItem->purchaseOrder?->supplier_id ?: $plate->supplier_id,
                    userId: auth()->id(),
                    nomorTransaksi: 'RET-' . ($return->no_return ?: $return->id),
                    catatan: 'Retur Eksternal ke Supplier (' . ($return->no_return ?: ('#' . $return->id)) . ')',
                    jenisTransaksi: 'OUT'
                );
                continue;
            }

            $stock = MaterialStock::firstOrCreate(
                [
                    'item_id' => $poItem->item_id,
                ],
                [
                    'on_hand' => 0,
                    'allocation' => 0,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Validasi Stock
            |--------------------------------------------------------------------------
            */

            if ($stock->on_hand < $item->qty_return) {

                throw ValidationException::withMessages([
                    'stock' => "Stock {$pbItem->purchaseOrderItem->item->nama_master_item} tidak mencukupi. Stock tersedia {$stock->on_hand}, return {$item->qty_return}."
                ]);

            }

            /*
            |--------------------------------------------------------------------------
            | Kurangi Stock
            |--------------------------------------------------------------------------
            */

            $stock->decrement(
                'on_hand',
                $item->qty_return
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Approve Return
        |--------------------------------------------------------------------------
        */

        $return->update([
            'status' => 'approved',
        ]);

    });
}

public function cancel(
    ExternalReturn $return
): void
{
    DB::transaction(function () use ($return) {

        if ($return->status != 'approved') {
            return;
        }

        $return->loadMissing(
            'items.penerimaanBarangItem.purchaseOrderItem'
        );

        foreach ($return->items as $item) {
            $poItem = $item->penerimaanBarangItem?->purchaseOrderItem;
            if (! $poItem) {
                continue;
            }

            $plate = \App\Models\MasterMaterialPlate::where('master_item_id', $poItem->item_id)->first();
            if ($plate) {
                app(\App\Services\Pracetak\PracetakStockService::class)->receiveFromLpb(
                    plate: $plate,
                    reference: $return,
                    qty: (float) $item->qty_return,
                    unitPrice: (float) ($poItem->price ?? 0),
                    supplierId: $poItem->purchaseOrder?->supplier_id ?: $plate->supplier_id,
                    userId: auth()->id(),
                    nomorTransaksi: 'CXL-RET-' . ($return->no_return ?: $return->id),
                    catatan: 'Batal Retur Eksternal (' . ($return->no_return ?: ('#' . $return->id)) . ')'
                );
                continue;
            }

            $stock = MaterialStock::firstOrCreate(
                [
                    'item_id' => $poItem->item_id,
                ],
                [
                    'on_hand' => 0,
                    'allocation' => 0,
                ]
            );

            $stock->increment(
                'on_hand',
                $item->qty_return
            );
        }

        $return->update([
            'status'=>'cancel'
        ]);

    });
}


 public function update(
    ExternalReturn $return
): void
{
    DB::transaction(function () use ($return) {

        /*
        |--------------------------------------------------------------------------
        | Jika sudah approved tidak boleh diedit
        |--------------------------------------------------------------------------
        */

        if ($return->status === 'approved') {

            throw new \Exception(
                'External Return sudah diapprove dan tidak dapat diubah.'
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Tidak ada proses stock.
        | Edit hanya menyimpan perubahan data.
        |--------------------------------------------------------------------------
        */

    });
}
}