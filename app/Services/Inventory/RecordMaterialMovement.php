<?php

namespace App\Services\Inventory;

use App\Models\MaterialMovement;

class RecordMaterialMovement
{
    public static function record(
        int $itemId,
        string $movementType,
        float $qty,
        float $before,
        float $after,
        ?string $remarks = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): MaterialMovement {

        return MaterialMovement::create([

            'item_id' => $itemId,

            'created_by' => auth()->id(),

            'reference_type' => $referenceType,

            'reference_id' => $referenceId,

            'movement_type' => strtoupper($movementType),

            'qty' => $qty,

            'stock_before' => $before,

            'stock_after' => $after,

            'remarks' => $remarks,

        ]);
    }
}