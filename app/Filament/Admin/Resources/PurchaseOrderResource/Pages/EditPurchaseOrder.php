<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\Pages;

use App\Filament\Admin\Resources\PurchaseOrderResource;
use Filament\Resources\Pages\EditRecord;
use App\Models\PurchaseRequest;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    public $prItems = [];

    public function mount($record): void
    {
        parent::mount($record);

        if ($this->record->purchase_request_id) {

            $pr = PurchaseRequest::with('items.item')
                ->find($this->record->purchase_request_id);

            $this->prItems = $pr?->items ?? [];
        }
    }
}