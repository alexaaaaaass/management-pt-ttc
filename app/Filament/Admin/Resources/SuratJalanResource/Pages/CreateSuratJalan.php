<?php

namespace App\Filament\Admin\Resources\SuratJalanResource\Pages;

use App\Filament\Admin\Resources\SuratJalanResource;
use App\Models\MaterialStock;
use App\Models\SalesOrder;
use App\Models\SuratJalan;
use App\Models\SuratJalanItem;
use App\Services\Inventory\RecordMaterialMovement;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateSuratJalan extends CreateRecord
{
    protected static string $resource = SuratJalanResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | Ambil Detail Repeater
            |--------------------------------------------------------------------------
            */
            $detailItems = $data['items'] ?? [];
            unset($data['items']);

            /*
            |--------------------------------------------------------------------------
            | Create Surat Jalan
            |--------------------------------------------------------------------------
            */
            $suratJalan = static::getModel()::create($data);

            /*
            |--------------------------------------------------------------------------
            | Ambil Sales Order
            |--------------------------------------------------------------------------
            */
            $salesOrder = SalesOrder::with('items')->find($suratJalan->sales_order_id);

            if (! $salesOrder) {
                return $suratJalan;
            }

            /*
            |--------------------------------------------------------------------------
            | Handle Berdasarkan Mode Sales Order
            |--------------------------------------------------------------------------
            */
            switch ($salesOrder->mode) {

                case 'SPK':
                    // Tidak ada proses material
                    break;

                case 'SINGLE_MATERIAL':
                    $item = $salesOrder->items->first();

                    if ($item && isset($data['qty_pengiriman'])) {
                        SuratJalanItem::create([
                            'surat_jalan_id'      => $suratJalan->id,
                            'sales_order_item_id' => $item->id,
                            'master_item_id'      => $item->master_item_id,
                            'qty_kirim'           => $data['qty_pengiriman'],
                        ]);

                        $this->updateMaterialStock(
                            $item->master_item_id,
                            (float) $data['qty_pengiriman'],
                            $suratJalan
                        );
                    }
                    break;

                case 'MULTI_MATERIAL':
                    foreach ($detailItems as $row) {
                        $qty = (float) ($row['qty_kirim'] ?? 0);

                        if ($qty <= 0) {
                            continue;
                        }

                        $detail = SuratJalanItem::create([
                            'surat_jalan_id'      => $suratJalan->id,
                            'sales_order_item_id' => $row['sales_order_item_id'],
                            'master_item_id'      => $row['master_item_id'],
                            'qty_kirim'           => $qty,
                        ]);

                        $this->updateMaterialStock(
                            $detail->master_item_id,
                            $qty,
                            $suratJalan
                        );
                    }
                    break;
            }

            return $suratJalan;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Update Stock Material
    |--------------------------------------------------------------------------
    */
    protected function updateMaterialStock(int $itemId, float $qty, SuratJalan $suratJalan): void
    {
        $stock = MaterialStock::where('item_id', $itemId)
            ->lockForUpdate()
            ->first();

        if (! $stock) {
            return;
        }

        $before = (float) $stock->on_hand;
        $after = $before - $qty;

        $stock->update([
            'on_hand' => $after,
        ]);

        RecordMaterialMovement::record(
            itemId: $itemId,
            movementType: 'OUT',
            qty: $qty,
            before: $before,
            after: $after,
            remarks: 'Pengiriman Material - ' . $suratJalan->kode_surat_jalan,
            referenceType: SuratJalan::class,
            referenceId: $suratJalan->id,
        );
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Surat Jalan berhasil dibuat')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return SuratJalanResource::getUrl('index');
    }
}