<?php

namespace App\Services\IMR\Helpers;

use App\Models\ImrPrinting;

class UpdateStatus
{
    public function handle(ImrPrinting $imr): void
    {
        $imr->refresh();

        $approved = $imr->items->sum('qty_approved');

        $request = $imr->items->sum('qty_request');

        if ($approved <= 0) {

            $imr->update([
                'status' => 'Rejected'
            ]);

            return;
        }

        if ($approved < $request) {

            $imr->update([
                'status' => 'Partial'
            ]);

            return;
        }

        $imr->update([
            'status' => 'Approved'
        ]);
    }
}