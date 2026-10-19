<?php

namespace App\Filament\Admin\Resources\OrderConfirmationResource\Pages;

use App\Filament\Admin\Resources\OrderConfirmationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderConfirmation extends CreateRecord
{
    protected static string $resource = OrderConfirmationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->recalculateTotals();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
