<?php

namespace App\Services\Inventory;

use App\Models\ImrPrinting;
use App\Services\IMR\Actions\IssueMaterial;

class IssueMaterialService
{
    public function __construct(

        protected IssueMaterial $issue,

    ) {}

    public function issue(
        ImrPrinting $imr,
        array $data
    ): void
    {
        $this->issue->handle($imr, $data);
    }
}