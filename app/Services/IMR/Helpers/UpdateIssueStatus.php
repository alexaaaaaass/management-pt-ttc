<?php

namespace App\Services\IMR\Actions;

use App\Models\ImrPrinting;

class UpdateIssueStatus
{
    public function handle(ImrPrinting $imr): void
    {
        $approved = $imr->items->sum('qty_approved');

        $issued = $imr->items->sum('qty_issued');

        if ($approved == 0) {

            $status = 'Approved';

        } elseif ($issued == 0) {

            $status = 'Approved';

        } elseif ($issued < $approved) {

            $status = 'Partial Issued';

        } else {

            $status = 'Issued';

        }

        $imr->update([
            'status' => $status
        ]);
    }
}