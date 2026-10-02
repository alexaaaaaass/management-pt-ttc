<?php

namespace App\Filament\Admin\Resources\SalesOrderResource\Pages;

use App\Filament\Admin\Resources\SalesOrderResource;
use App\Models\FinishGoodItem;
use App\Models\MaterialStock;
use App\Models\SalesOrderItem;

use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateSalesOrder extends CreateRecord
{
    protected static string $resource = SalesOrderResource::class;

    protected array $materialItems = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /*
        |--------------------------------------------------------------------------
        | SIMPAN DATA REPEATER
        |--------------------------------------------------------------------------
        */

        $this->materialItems = $data['items'] ?? [];

        unset($data['items']);

        /*
        |--------------------------------------------------------------------------
        | MATERIAL
        |--------------------------------------------------------------------------
        */

        if (($data['order_type'] ?? 'SPK') === 'MATERIAL') {

            $data['itemable_type'] = null;
            $data['itemable_id'] = null;
            $data['qty'] = null;
            $data['harga_pcs'] = null;
            $data['kode_material'] = null;

            return $data;
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDASI BOM SPK
        |--------------------------------------------------------------------------
        */

        if ($data['itemable_type'] === FinishGoodItem::class) {

            $finishGood = FinishGoodItem::with(
                'materials.material'
            )->find($data['itemable_id']);

            foreach ($finishGood->materials as $bom) {

                $kebutuhan = $data['qty'] * $bom->qty;

                $stock = MaterialStock::where(
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
                            ($bom->material->nama_master_item ?? '-')
                            . " | Butuh {$kebutuhan} | Sisa {$available}"
                        )
                        ->danger()
                        ->send();

                    $this->halt();
                }
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        /*
        |--------------------------------------------------------------------------
        | SIMPAN DETAIL MATERIAL
        |--------------------------------------------------------------------------
        */

        if ($this->record->order_type !== 'MATERIAL') {
            return;
        }

        foreach ($this->materialItems as $item) {

            SalesOrderItem::create([

                'sales_order_id' => $this->record->id,

                'master_item_id' => $item['master_item_id'],

                'qty' => $item['qty'],

                'harga' => $item['harga'],

            ]);
        }
    }
}