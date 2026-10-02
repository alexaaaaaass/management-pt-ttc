<?php

namespace App\Services\IMR\Validators;

use App\Models\ImrPrinting;
use App\Models\MaterialStock;
use Illuminate\Validation\ValidationException;

class ValidateApproval
{
    public function handle(ImrPrinting $imr, array $data): void
    {
        foreach ($imr->items as $index => $item) {

            $qtyApproved = (float) ($data['items'][$index]['qty_approved'] ?? 0);

            if ($qtyApproved < 0) {

                throw ValidationException::withMessages([
                    "items.$index.qty_approved" =>
                        'Qty approve tidak boleh minus.',
                ]);

            }

            if ($qtyApproved > $item->qty_request) {

                throw ValidationException::withMessages([
                    "items.$index.qty_approved" =>
                        'Qty approve melebihi qty request.',
                ]);

            }

            $stock = MaterialStock::where(
                'item_id',
                $item->item_id
            )
            ->lockForUpdate()
            ->first();

            if (! $stock) {

                throw ValidationException::withMessages([
                    'stock' => "Stock {$item->item->nama_master_item} tidak ditemukan.",
                ]);

            }

            if ($stock->on_hand < $qtyApproved) {

                throw ValidationException::withMessages([
                    'stock' =>
                        "{$item->item->nama_master_item} hanya tersedia {$stock->on_hand}",
                ]);

            }

        }
    }
}