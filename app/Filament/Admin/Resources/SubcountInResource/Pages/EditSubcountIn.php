<?php

namespace App\Filament\Admin\Resources\SubcountInResource\Pages;

use App\Filament\Admin\Resources\SubcountInResource;
use App\Services\Subcontract\SubcontractService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubcountIn extends EditRecord
{
    protected static string $resource = SubcountInResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->loadMissing([
            'subcountOut.supplier',
            'supplier',
            'items.itemAsal',
            'items.spk.salesOrder.itemable',
            'items.subcountOut',
        ]);

        $resolvedOut = $this->record->resolved_subcount_out;
        $data['subcount_out_id'] = $data['subcount_out_id'] ?? $resolvedOut?->id;
        $data['supplier_id'] = $data['supplier_id'] ?? $this->record->resolved_supplier?->id;

        $data['items'] = $this->record->items->map(function ($item): array {
            $arr = $item->toArray();
            $arr['nama_item_asal'] = $item->itemAsal?->nama_master_item
                ?? ($item->spk?->salesOrder?->itemable?->nama_barang
                    ?? $item->spk?->salesOrder?->product_name
                    ?? $item->spk?->no_spk
                    ?? '-');

            return $arr;
        })->all();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->using(fn ($record) => app(SubcontractService::class)->deleteSubcountIn($record)),
        ];
    }
}
