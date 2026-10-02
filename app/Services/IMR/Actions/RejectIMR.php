<?php

namespace App\Services\IMR\Actions;

use App\Models\ImrPrinting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RejectIMR
{
    public function handle(ImrPrinting $imr): void
    {
        DB::transaction(function () use ($imr) {

            if ($imr->status !== 'Pending') {

                throw ValidationException::withMessages([
                    'status' => 'IMR sudah diproses.'
                ]);

            }

            $imr->update([
                'status' => 'Rejected',
            ]);

        });
    }
}