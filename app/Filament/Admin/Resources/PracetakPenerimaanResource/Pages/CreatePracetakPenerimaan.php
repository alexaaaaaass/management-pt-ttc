<?php

namespace App\Filament\Admin\Resources\PracetakPenerimaanResource\Pages;

use App\Filament\Admin\Resources\PracetakPenerimaanResource;
use App\Models\PracetakPenerimaan;
use Filament\Resources\Pages\CreateRecord;

class CreatePracetakPenerimaan extends CreateRecord
{
    protected static string $resource = PracetakPenerimaanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status'] = 'draft';

        // Hitung total qty dan nominal awal
        $totalQty = 0;
        $totalNominal = 0;
        if (!empty($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $qty = (float) ($item['qty_sheet'] ?? 0);
                $sub = (float) ($item['subtotal'] ?? 0);
                $totalQty += $qty;
                $totalNominal += $sub;
            }
        }
        $data['total_qty'] = $totalQty;
        $data['total_nominal'] = $totalNominal;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
