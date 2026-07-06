<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\Pages;

use App\Filament\Admin\Resources\PurchaseOrderResource;
use App\Services\PurchaseOrder\PurchaseOrderService;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected array $items = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->items = $data['items'] ?? [];

        unset($data['items']);

        return $data;
    }

    protected function afterCreate(): void
    {
        app(PurchaseOrderService::class)
            ->create($this->record, $this->items);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', [
            'record' => $this->record,
        ]);
    }
}