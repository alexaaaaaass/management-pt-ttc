<?php

namespace App\Services\IMR\Actions;

use App\Models\ImrPrinting;

class CloseIMR
{
    public function handle(ImrPrinting $imr): void
    {
        if(!in_array($imr->status,[
            'Approved',
            'Partial'
        ])){
            return;
        }

        $imr->update([
            'status'=>'Closed'
        ]);
    }
}