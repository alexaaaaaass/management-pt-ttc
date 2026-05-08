<?php

namespace App\Filament\Admin\Resources\SalesOrderResource\Pages;

use App\Filament\Admin\Resources\SalesOrderResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;


class CreateSalesOrder extends CreateRecord
{
    protected static string $resource = SalesOrderResource::class;

   protected function mutateFormDataBeforeCreate(array $data): array
{
    if ($data['itemable_type'] === \App\Models\FinishGoodItem::class) {

        $finishGood = \App\Models\FinishGoodItem::with('materials.material')
            ->find($data['itemable_id']);

        foreach ($finishGood->materials as $bom) {

            $kebutuhan = $data['qty'] * $bom->qty;

            $stock = \App\Models\MaterialStock::where(
                'item_id',
                $bom->master_item_id
            )->first();

            $available = $stock
                ? ($stock->on_hand - $stock->allocation)
                : 0;

            if ($kebutuhan > $available) {

                Notification::make()
                    ->title('Stock Tidak Cukup')
                    ->body(
                        ($bom->material->nama_master_item ?? '-') .
                        " | Butuh: $kebutuhan | Sisa: $available"
                    )
                    ->danger()
                    ->send();

                $this->halt();
            }
        }
    }

    return $data;
}
}