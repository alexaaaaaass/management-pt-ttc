<?php

namespace App\Filament\Admin\Resources\PurchaseRequestResource\Pages;

use App\Filament\Admin\Resources\PurchaseRequestResource;
use App\Services\PurchaseRequest\PurchaseRequestService;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseRequest extends CreateRecord
{
    protected static string $resource = PurchaseRequestResource::class;

        protected function mutateFormDataBeforeCreate(array $data): array
    {
        app(PurchaseRequestService::class)
            ->generateNumber($data);

        return $data;
    }
}