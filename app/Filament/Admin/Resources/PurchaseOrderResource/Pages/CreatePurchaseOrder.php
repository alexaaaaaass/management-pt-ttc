<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\Pages;

    use App\Filament\Admin\Resources\PurchaseOrderResource;
    use Filament\Resources\Pages\CreateRecord;
    use App\Models\PurchaseRequest;
    use App\Models\PurchaseOrderItem;

    class CreatePurchaseOrder extends CreateRecord
    {
        protected static string $resource = PurchaseOrderResource::class;

        public $prItems = [];

       public function getPRItems($prId)
    {
        $pr = PurchaseRequest::with('items.item')->find($prId);

        if ($pr) {
            $this->prItems = $pr->items;
        } else {
            $this->prItems = [];
        }
    }
    protected function afterCreate(): void
    {
        $po = $this->record;

        foreach ($this->prItems as $item) {

            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'item_id' => $item->item_id,
                'qty' => $item->qty,
                'satuan' => $item->satuan,
                'eta' => $item->eta,
                'catatan' => $item->catatan,
            ]);

        }
    }


    }