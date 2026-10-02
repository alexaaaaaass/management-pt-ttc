<?php

namespace App\Services\IMR\Actions;

use App\Models\ImrPrinting;
use Illuminate\Validation\ValidationException;

class DeleteIMR
{
    public function handle(ImrPrinting $imr): void
    {
        if($imr->status=='Approved'){

            throw ValidationException::withMessages([
                'status'=>'IMR Approved tidak boleh dihapus.'
            ]);

        }

        $imr->delete();
    }
}