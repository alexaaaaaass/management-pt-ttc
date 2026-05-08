<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\Pages;

use App\Filament\Admin\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

    class CreatePurchaseOrder extends CreateRecord
    {
        protected static string $resource = PurchaseOrderResource::class;

    //     public $prItems = [];

    //    public function getPRItems($prId)
    // {
    //     $pr = PurchaseRequest::with('items.item')->find($prId);

    //     if ($pr) {
    //         $this->prItems = $pr->items;
    //     } else {
    //         $this->prItems = [];
    //     }
    // }

protected function afterCreate(): void
{
    $items = $this->items ?? [];

    foreach ($items as $item) {
        $this->record->items()->create($item);
    }
}
protected function getRedirectUrl(): string
{
    return $this->getResource()::getUrl('edit', [
        'record' => $this->record,
    ]);
}
protected function mutateFormDataBeforeCreate(array $data): array
{
    $this->items = $data['items'] ?? [];
    unset($data['items']);

    return $data;
}


    }