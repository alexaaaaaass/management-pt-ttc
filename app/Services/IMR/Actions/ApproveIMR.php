<?php

namespace App\Services\IMR\Actions;

use App\Models\ImrPrinting;
use App\Models\MaterialStock;
use App\Services\IMR\Helpers\UpdateStatus;
use App\Services\IMR\Validators\ValidateApproval;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\MaterialMovement\RecordMaterialMovement;

class ApproveIMR
{
    public function __construct(
        protected ValidateApproval $validator,
        protected UpdateStatus $statusUpdater,
        protected RecordMaterialMovement $movement,
    ) {}

    public function handle(
        ImrPrinting $imr,
        array $data
    ): void {

        DB::transaction(function () use ($imr, $data) {

            if ($imr->status !== 'Pending') {

                throw ValidationException::withMessages([
                    'status' => 'IMR sudah diproses.',
                ]);

            }

            $imr->loadMissing('items');

            $this->validator->handle(
                $imr,
                $data
            );

            foreach ($imr->items as $index => $item) {

    $qtyApproved = (float) (
        $data['items'][$index]['qty_approved'] ?? 0
    );

    $stock = MaterialStock::where(
        'item_id',
        $item->item_id
    )
    ->lockForUpdate()
    ->first();

    if (! $stock) {

        throw new \Exception(
            "Stock {$item->item->nama_master_item} tidak ditemukan."
        );

    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI AVAILABLE
    |--------------------------------------------------------------------------
    */

    if ($qtyApproved > $stock->available) {

        throw new \Exception(
            "Available Stock {$item->item->nama_master_item} hanya {$stock->available}"
        );

    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE ITEM
    |--------------------------------------------------------------------------
    */

    $item->update([

        'qty_approved' => $qtyApproved

    ]);

    /*
    |--------------------------------------------------------------------------
    | RESERVE STOCK
    |--------------------------------------------------------------------------
    */

    $stock->increment(
        'allocation',
        $qtyApproved
    );

   $this->movement->handle([

    'item_id'        => $item->item_id,

    'reference_type' => 'IMR',

    'reference_id'   => $imr->id,

    'movement_type'  => 'APPROVE',

    'qty'            => $qtyApproved,

    'stock_before'   => $stock->on_hand,

    'stock_after'    => $stock->on_hand,

    'remarks'        => "Approve IMR {$imr->no_imr}",

]);

}
            $this->statusUpdater->handle($imr);

        });

    }
}