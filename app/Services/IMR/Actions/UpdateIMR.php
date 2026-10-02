<?php

namespace App\Services\IMR\Actions;

use App\Models\ImrPrinting;
use Illuminate\Validation\ValidationException;

class UpdateIMR
{
    public function handle(ImrPrinting $imr): void
    {
        if($imr->status=='Closed'){

            throw ValidationException::withMessages([
                'status'=>'IMR Closed tidak bisa diedit.'
            ]);

        }
    }
}