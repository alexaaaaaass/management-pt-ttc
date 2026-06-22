<?php

namespace App\Filament\Admin\Resources\SubcountOutResource\Pages;

use App\Filament\Admin\Resources\SubcountOutResource;
use App\Models\MaterialStock;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateSubcountOut extends CreateRecord
{
    protected static string $resource = SubcountOutResource::class;

    protected function afterCreate(): void
    {
        DB::transaction(function () {

            $this->record->load('items');

            foreach ($this->record->items as $item) {

                if ($item->sumber_item !== 'material') {
                    continue;
                }

                $stock = MaterialStock::where(
                    'item_id',
                    $item->item_id
                )->lockForUpdate()
                 ->first();

                if (!$stock) {
                    continue;
                }

                // validasi stok
                if ($stock->on_hand < $item->qty) {

                    throw new \Exception(
                        "Stok {$item->item->nama_master_item} tidak mencukupi."
                    );
                }

                // keluar dari gudang
                $stock->decrement('on_hand', $item->qty);

                // masuk proses subcount
                $stock->increment('allocation', $item->qty);
            }

        });
    }
}