<?php

namespace App\Filament\Admin\Resources\PoBillingResource\Pages;

use App\Filament\Admin\Resources\PoBillingResource;
use App\Services\Finance\PoBillingService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePoBilling extends CreateRecord
{
    protected static string $resource = PoBillingResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(PoBillingService::class)->createBilling($data);
    }
}
