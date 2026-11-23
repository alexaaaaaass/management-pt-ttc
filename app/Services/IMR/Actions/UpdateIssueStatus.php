<?php

namespace App\Services\IMR\Actions;

use App\Models\ImrPrinting;

class UpdateIssueStatus
{
    public function handle(ImrPrinting $imr): void
    {
        $imr->refresh();
        $imr->loadMissing('items');

        $approved = (float) $imr->items->sum('qty_approved');
        $issued = (float) $imr->items->sum('qty_issued');

        $materialPending = $approved > 0 && $issued < $approved;
        $platePending = $imr->hasPendingPlateIssue();
        $anyIssued = ($issued > 0) || $imr->isPlateIssued();

        if (! $anyIssued) {
            $status = 'Approved';
        } elseif ($materialPending || $platePending) {
            $status = 'Partial Issued';
        } else {
            $status = 'Issued';
        }

        $imr->update([
            'status' => $status,
        ]);
    }
}