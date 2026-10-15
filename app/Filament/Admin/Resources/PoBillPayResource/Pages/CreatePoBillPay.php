<?php

namespace App\Filament\Admin\Resources\PoBillPayResource\Pages;

use App\Filament\Admin\Resources\PoBillPayResource;
use App\Services\Finance\PoBillPayService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePoBillPay extends CreateRecord
{
    protected static string $resource = PoBillPayResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(PoBillPayService::class)->recordPayment($data);
    }
}
