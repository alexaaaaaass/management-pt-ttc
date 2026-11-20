<?php

namespace App\Filament\Admin\Resources\PracetakProcessResource\Pages;

use App\Filament\Admin\Resources\PracetakProcessResource;
use App\Models\ApprovalForm;
use App\Models\MasterSpk;
use App\Services\Pracetak\PracetakCostingService;
use Filament\Resources\Pages\CreateRecord;

class CreatePracetakProcess extends CreateRecord
{
    protected static string $resource = PracetakProcessResource::class;

    public function mount(): void
    {
        parent::mount();

        $spkId = request()->query('spk_id');
        $afId = request()->query('approval_form_id');

        if ($spkId && ($spk = MasterSpk::find($spkId))) {
            $resolved = app(PracetakCostingService::class)->resolveEstimatedPlateFromSpk($spk);
            $this->form->fill([
                'spk_id'                => $spk->id,
                'approval_form_id'      => $resolved['approval_form_id'],
                'order_confirmation_id' => $resolved['order_confirmation_id'],
                'finish_good_item_id'   => $resolved['finish_good_item_id'],
                'nama_plat'             => $resolved['nama_plat'],
                'qty'                   => $resolved['jumlah_warna_plate'],
                'jumlah_warna_plate'    => $resolved['jumlah_warna_plate'],
                'estimasi_biaya_plate'  => $resolved['estimasi_biaya_plate'],
                'is_repeat_order'       => $resolved['is_repeat_order'],
                'plate_archive_id'      => $resolved['plate_archive_id'],
                'ket_spk'               => $spk->is_subcount ? 'subcount' : 'reguler',
                'status'                => $resolved['is_repeat_order'] ? 'selesai' : 'design',
            ]);
        } elseif ($afId && ($af = ApprovalForm::with('items')->find($afId))) {
            $firstItem = $af->items->first();
            $warnaCount = 4;
            if ($firstItem?->warna && preg_match('/\d+/', (string) $firstItem->warna, $m)) {
                $warnaCount = max(1, (int) $m[0]);
            }
            $this->form->fill([
                'approval_form_id'    => $af->id,
                'finish_good_item_id' => $firstItem?->finish_good_item_id,
                'nama_plat'           => $firstItem?->item_name ?: "Pre-Order {$af->form_no}",
                'qty'                 => $warnaCount,
                'jumlah_warna_plate'  => $warnaCount,
                'ket_spk'             => 'reguler',
                'status'              => 'design',
            ]);
        }
    }

    protected function afterCreate(): void
    {
        app(PracetakCostingService::class)->recalculateProcessCost($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
