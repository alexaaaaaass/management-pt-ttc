<?php

namespace App\Services\MaterialMovement;

use App\Models\MaterialMovement;

class RecordMaterialMovement
{
    public function handle(array $data): MaterialMovement
    {
        return MaterialMovement::create([

            'item_id'         => $data['item_id'],

            'user_id'         => $data['user_id'] ?? auth()->id(),

            'reference_type'  => $data['reference_type'],

            'reference_id'    => $data['reference_id'],

            'movement_type'   => strtoupper($data['movement_type']),

            'qty'             => (float) $data['qty'],

            'stock_before'    => (float) ($data['stock_before'] ?? 0),

            'stock_after'     => (float) ($data['stock_after'] ?? 0),

            'remarks'         => $data['remarks'] ?? null,

        ]);
    }
}