<?php

namespace App\Filament\Admin\Resources\OrderConfirmationResource\Pages;

use App\Filament\Admin\Resources\OrderConfirmationResource;
use App\Models\ApprovalForm;
use App\Models\FinishGoodItem;
use App\Models\OrderConfirmation;
use App\Services\Costing\FlexoHppCalculatorService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateOrderConfirmation extends CreateRecord
{
    protected static string $resource = OrderConfirmationResource::class;

    public function mount(): void
    {
        parent::mount();

        $afId = request()->query('approval_form_id') ?: request()->query('af_id');
        if ($afId) {
            $af = ApprovalForm::with(['customer', 'items.finishGoodItem'])->find($afId);
            if ($af) {
                $newItems = [];
                foreach ($af->items as $item) {
                    $fgId = $item->finish_good_item_id;
                    if (!$fgId) {
                        $fgId = FinishGoodItem::where('nama_barang', $item->item_name)
                            ->where('customer_id', $af->customer_id)
                            ->value('id');
                    }

                    $uuid = (string) Str::uuid();
                    $targetQty = (float) $item->qty ?: 100000;
                    $fg = $fgId ? FinishGoodItem::find($fgId) : null;
                    $panjang = $fg?->panjang ? (float) $fg->panjang : 178.0;
                    $lebar = $fg?->lebar ? (float) $fg->lebar : 119.0;
                    $gramatur = $fg?->gramasi ? (float) $fg->gramasi : FlexoHppCalculatorService::DEFAULT_GRAMATUR;

                    $calc = app(FlexoHppCalculatorService::class)->calculate([
                        'panjang_etiket_jadi' => $panjang,
                        'lebar_etiket_jadi' => $lebar,
                        'gramatur_kertas' => $gramatur,
                    ], $targetQty);

                    $calcPrice = (float) $calc['harga_jual_indikatif'] > 0 ? (float) $calc['harga_jual_indikatif'] : (float) $calc['hpp_per_etiket'];
                    $itemQty = (float) $item->qty;
                    $rowTotal = $itemQty * $calcPrice;

                    $newItems[$uuid] = [
                        'approval_form_item_id' => $item->id,
                        'finish_good_item_id'   => $fgId,
                        'item_name'             => $item->item_name,
                        'jenis'                 => $item->jenis,
                        'komponen'              => $item->komponen,
                        'isi'                   => $item->isi,
                        'kertas'                => $item->kertas,
                        'warna'                 => $item->warna,
                        'finishing'             => $item->finishing,
                        'qty'                   => $itemQty,
                        'harga'                 => $calcPrice,
                        'total'                 => $rowTotal,
                        'satuan'                => $item->satuan ?: 'Pcs',
                        'hpp_per_pcs'           => $calc['hpp_per_etiket'],
                        'harga_jual_indikatif'  => $calc['harga_jual_indikatif'],
                        'total_biaya_order'     => $calc['total_biaya_order'],
                        'biaya_bahan'           => $calc['biaya_bahan'],
                        'biaya_proses'          => $calc['biaya_proses_cetak_total'],
                        'kebutuhan_kertas_kg'   => $calc['berat_bahan_teoritis'],
                        'kebutuhan_kertas_meter'=> $calc['total_panjang_bahan'],
                        'kalkulasi_hpp_data'    => $calc,
                    ];
                }

                $subtotal = 0;
                foreach ($newItems as $it) {
                    $subtotal += ((float)$it['qty'] * (float)$it['harga']);
                }

                $this->form->fill([
                    'approval_form_id'   => $af->id,
                    'customer_id'        => $af->customer_id,
                    'order_id'           => $af->order_id,
                    'lead_time'          => $af->lead_time ?: '21 Hari Kerja',
                    'doc_no'             => $af->customer ? OrderConfirmation::generateDocNo($af->customer) : null,
                    'disetujui_customer' => $af->customer?->nama_customer,
                    'items'              => $newItems,
                    'subtotal'           => $subtotal,
                    'grand_total'        => $subtotal,
                ]);
            }
        }
    }

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
