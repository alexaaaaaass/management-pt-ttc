<?php

namespace App\Services\PurchaseRequest;

use App\Models\PurchaseRequest;
use Carbon\Carbon;

class PurchaseRequestService
{
    public function generateNumber(array &$data): void
    {
        $tanggal = $data['tanggal_pr'] ?? now();

        $date = Carbon::parse($tanggal)->format('Ymd');

        $count = PurchaseRequest::whereDate(
            'tanggal_pr',
            $tanggal
        )->count() + 1;

        $data['nomor_pr'] =
            'PR-'.$date.'-'.str_pad($count,3,'0',STR_PAD_LEFT);

        $data['status']='deotorisasi';
    }
}