<?php

namespace App\Services\IMR;

use App\Models\ImrPrinting;
use App\Services\IMR\Actions\ApproveIMR;
use App\Services\IMR\Actions\CloseIMR;
use App\Services\IMR\Actions\CreateIMR;
use App\Services\IMR\Actions\DeleteIMR;
use App\Services\IMR\Actions\RejectIMR;
use App\Services\IMR\Actions\UpdateIMR;
use App\Services\IMR\Actions\IssueMaterial;

class IMRPrintingService
{
    public function __construct(
        protected CreateIMR $create,
        protected ApproveIMR $approve,
        protected RejectIMR $reject,
        protected CloseIMR $close,
        protected DeleteIMR $delete,
        protected UpdateIMR $update,
        protected IssueMaterial $issue,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(array $data): ImrPrinting
    {
        return $this->create->handle($data);
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        ImrPrinting $imr,
        array $data
    ): void {
        $this->approve->handle($imr, $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        ImrPrinting $imr
    ): void {
        $this->reject->handle($imr);
    }

    /*
    |--------------------------------------------------------------------------
    | Close
    |--------------------------------------------------------------------------
    */

    public function close(
        ImrPrinting $imr
    ): void {
        $this->close->handle($imr);
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        ImrPrinting $imr
    ): void {
        $this->update->handle($imr);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        ImrPrinting $imr
    ): void {
        $this->delete->handle($imr);
    }

     /*
    |--------------------------------------------------------------------------
    | Isssue Material
    |--------------------------------------------------------------------------
    */
   public function issue(
    ImrPrinting $imr,
    array $data
    ): void {

        $this->issue->handle($imr,$data);

    }

}