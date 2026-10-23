<?php

namespace App\Filament\Admin\Resources\SalesOrderResource\Pages;

use App\Filament\Admin\Resources\SalesOrderResource;
use App\Models\FinishGoodItem;
use App\Models\OrderConfirmation;
use App\Services\Sales\SalesOrderService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateSalesOrder extends CreateRecord
{
    protected static string $resource = SalesOrderResource::class;

    public ?int $orderConfirmationId = null;

    public function mount(): void
    {
        parent::mount();

        $ocId = request()->query('oc_id');
        if ($ocId) {
            $this->orderConfirmationId = (int) $ocId;
            $oc = OrderConfirmation::with(['customer', 'items.finishGoodItem'])->find($ocId);
            if ($oc) {
                $firstItem = $oc->items->first();
                $fgId = $firstItem?->finish_good_item_id;
                if (! $fgId && $firstItem?->item_name) {
                    $fg = FinishGoodItem::where('nama_barang', $firstItem->item_name)->first();
                    $fgId = $fg?->id;
                }
                $fg = $fgId ? FinishGoodItem::find($fgId) : null;
                $qty = (float) ($oc->items->sum('qty') ?: ($firstItem?->qty ?? 0));
                $hargaPcs = (float) ($firstItem?->harga ?? 0);

                $bom = ($fg && $qty > 0)
                    ? app(SalesOrderService::class)->getBomPreview((int) $fg->id, $qty)
                    : [];

                $this->form->fill([
                    'order_confirmation_id' => $oc->id,
                    'order_type'            => 'SPK',
                    'customer_id'           => $oc->customer_id,
                    'item_source'           => $fg?->id,
                    'itemable_id'           => $fg?->id,
                    'itemable_type'         => $fg ? FinishGoodItem::class : null,
                    'product_name'          => $firstItem?->item_name ?: $fg?->nama_barang,
                    'no_sales_order'        => $oc->order_id ?: app(SalesOrderService::class)->generateNoSalesOrder(),
                    'tanggal_po'            => $oc->tanggal ? $oc->tanggal->format('Y-m-d') : now()->format('Y-m-d'),
                    'lead_time'             => $oc->lead_time ?: '21 Hari Kerja',
                    'qty'                   => $qty,
                    'harga_pcs'             => $hargaPcs,
                    'harga_kirim'           => 0,
                    'diskon'                => (float) ($oc->diskon ?? 0),
                    'mata_uang'             => 'rupiah',
                    'syarat_pembayaran'     => '30 Hari setelah invoice',
                    'toleransi_pengiriman'  => 0,
                    'klaim_kertas'          => 'not_claim',
                    'catatan'               => $oc->catatan,
                    'jenis'                 => $firstItem?->jenis ?: $fg?->jenis,
                    'komponen'              => $firstItem?->komponen ?: $fg?->komponen,
                    'isi'                   => $firstItem?->isi ?: $fg?->isi,
                    'kertas'                => $firstItem?->kertas ?: $fg?->spesifikasi_kertas,
                    'warna'                 => $firstItem?->warna ?: $fg?->warna,
                    'finishing'             => $firstItem?->finishing ?: $fg?->finishing,
                    'bom_items'             => $bom,
                ]);

                Notification::make()
                    ->title('Data Order Confirmation Dimuat')
                    ->body("Form terisi otomatis dari Order Confirmation {$oc->doc_no}. Silakan tinjau dan lengkapi data yang diperlukan sebelum menyimpan.")
                    ->info()
                    ->send();
            }
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        $materialItems = $data['items'] ?? [];
        unset($data['items']);

        if (! empty($data['order_confirmation_id'])) {
            $this->orderConfirmationId = (int) $data['order_confirmation_id'];
        }
        unset($data['order_confirmation_id']);
        unset($data['bom_items']);

        try {
            return app(SalesOrderService::class)->createSalesOrder($data, $materialItems);
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->implode(' | ');
            Notification::make()
                ->title('Gagal Menyimpan Sales Order')
                ->body($msg)
                ->danger()
                ->send();

            $this->halt();
            throw $e;
        }
    }

    protected function afterCreate(): void
    {
        $ocId = $this->orderConfirmationId;
        if ($ocId) {
            $oc = OrderConfirmation::find($ocId);
            if ($oc) {
                $oc->update([
                    'sales_order_id' => $this->record->id,
                    'status'         => 'converted_to_so',
                ]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}