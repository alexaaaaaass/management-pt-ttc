<?php

namespace App\Services\IMR\Helpers;

use App\Models\ImrPrinting;

class UpdateStatus
{
    public function handle(ImrPrinting $imr): void
    {
        $imr->refresh();
        $imr->loadMissing('items');

        $approved = (float) $imr->items->sum('qty_approved');
        $request = (float) $imr->items->sum('qty_request');
        $plateRequested = (bool) $imr->request_plate;
        $plateApproved = $plateRequested && (bool) $imr->plate_approved;

        if ($approved <= 0 && ! $plateApproved) {
            $imr->update([
                'status' => 'Rejected',
            ]);

            return;
        }

        $materialPartial = $request > 0 && $approved < $request;
        $plateRejected = $plateRequested && ! $plateApproved;

        if ($materialPartial || $plateRejected) {
            $imr->update([
                'status' => 'Partial',
            ]);

            return;
        }

        $imr->update([
            'status' => 'Approved',
        ]);
    }
}