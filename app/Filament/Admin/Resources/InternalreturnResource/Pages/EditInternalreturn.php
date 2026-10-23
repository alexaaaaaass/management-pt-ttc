<?php

namespace App\Filament\Admin\Resources\InternalreturnResource\Pages;

use App\Filament\Admin\Resources\InternalreturnResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInternalreturn extends EditRecord
{
    protected static string $resource = InternalreturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->status === 'draft'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $imr = $this->record->imr()->with(['spk', 'departemen', 'items.item.satuan'])->first();
        if ($imr) {
            $data['spk_display'] = $imr->spk?->no_spk ?? '-';
            $data['departemen_display'] = $imr->departemen?->nama_departemen ?? '-';
            $data['tanggal_request_display'] = $imr->tanggal_request ? date('d/m/Y', strtotime($imr->tanggal_request)) : '-';

            $imrItemsById = $imr->items->keyBy('id');
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $idx => $item) {
                    $imrItem = $imrItemsById->get($item['imr_printing_item_id'] ?? null);
                    if ($imrItem) {
                        $available = max(0, (float) $imrItem->qty_issued - ((float) $imrItem->qty_returned - (float) ($item['qty_return'] ?? 0)));
                        $data['items'][$idx]['nama_item'] = ($imrItem->item->kode_material ?? '-') . ' - ' . ($imrItem->item->nama_master_item ?? '-');
                        $data['items'][$idx]['qty_issued'] = (float) $imrItem->qty_issued;
                        $data['items'][$idx]['qty_available'] = $available;
                        $data['items'][$idx]['satuan'] = $imrItem->item?->satuan?->nama_satuan ?? $imrItem->satuan ?? '-';
                    }
                }
            }
        }

        return $data;
    }
}
