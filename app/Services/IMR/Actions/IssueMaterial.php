<?php

namespace App\Services\IMR\Actions;

use App\Models\ImrPrinting;
use App\Models\MaterialStock;
use App\Services\IMR\Actions\UpdateIssueStatus;
use App\Services\MaterialMovement\RecordMaterialMovement;
use Illuminate\Support\Facades\DB;

class IssueMaterial
{
   public function __construct(

    protected UpdateIssueStatus $statusUpdater,

    protected RecordMaterialMovement $movement,

){}

    public function handle(
        ImrPrinting $imr,
        array $data
    ): void {

        DB::transaction(function () use ($imr, $data) {

            $imr->loadMissing([
                'items.item.stock',
            ]);

            foreach ($imr->items as $index => $item) {

                $qtyIssue = (float) (
                    $data['items'][$index]['qty_issue'] ?? 0
                );

                if ($qtyIssue <= 0) {
                    continue;
                }

                $remaining =
                    $item->qty_approved -
                    $item->qty_issued;

                if ($qtyIssue > $remaining) {

                    throw new \Exception(
                        "Qty Issue {$item->item->nama_master_item} melebihi Qty Approved."
                    );

                }

                /*
                |--------------------------------------------------------------------------
                | Update Qty Issued
                |--------------------------------------------------------------------------
                */

                $item->increment(
                    'qty_issued',
                    $qtyIssue
                );

                /*
                |--------------------------------------------------------------------------
                | Update Material Stock
                |--------------------------------------------------------------------------
                */

               $stock = MaterialStock::where(
    'item_id',
    $item->item_id
)
->lockForUpdate()
->first();

if (! $stock) {

    throw new \Exception(
        "Stock material tidak ditemukan."
    );

}

$available = $stock->available;

if ($qtyIssue > $available) {

    throw new \Exception(
        "Material {$item->item->nama_master_item} hanya tersedia {$available} {$item->satuan}."
    );

}

/*
|--------------------------------------------------------------------------
| Baru update qty issued
|--------------------------------------------------------------------------
*/

$item->increment(
    'qty_issued',
    $qtyIssue
);
                /*
                On Hand berkurang
                */

                $stockBefore = $stock->on_hand;

$stock->decrement('on_hand',$qtyIssue);

$stock->decrement('allocation',$qtyIssue);

$stock->refresh();

$stockAfter = $stock->on_hand;

                $this->movement->handle([

    'item_id'        => $item->item_id,

    'reference_type' => 'IMR',

    'reference_id'   => $imr->id,

    'movement_type'  => 'ISSUE',

    'qty'            => -$qtyIssue,

    'stock_before'   => $stockBefore,

    'stock_after'    => $stockAfter,

    'remarks'        => "Issue Material {$imr->no_imr}",

]);

            }

            /*
            |--------------------------------------------------------------------------
            | Update Status
            |--------------------------------------------------------------------------
            */

            $this->statusUpdater
                ->handle($imr);

        });

    }
}