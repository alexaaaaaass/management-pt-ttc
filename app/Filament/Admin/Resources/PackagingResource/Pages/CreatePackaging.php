<?php

namespace App\Filament\Admin\Resources\PackagingResource\Pages;

use App\Filament\Admin\Resources\PackagingResource;
use App\Services\Production\ProductionService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePackaging extends CreateRecord
{
    protected static string $resource = PackagingResource::class;

    public function mount(): void
    {
        parent::mount();

        $spkId = request()->query('spk_id');
        if (! $spkId) {
            return;
        }

        $data = $this->form->getRawState();
        $data['spk_id'] = (int) $spkId;

        $jenis = request()->query('jenis_transfer');
        if (in_array($jenis, ['barang_hasil_baik', 'buffer_stock', 'label_kuning', 'blokir'], true)) {
            $data['jenis_transfer'] = $jenis;
        }

        $qty = (int) request()->query('qty', 0);
        if ($qty > 0) {
            $data['satuan_transfer'] = 'Pack';
            $data['jumlah_satuan_penuh'] = 0;
            $data['qty_per_satuan_penuh'] = 0;
            $data['jumlah_satuan_sisa'] = 1;
            $data['qty_per_satuan_sisa'] = $qty;
        }

        $this->form->fill($data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(ProductionService::class)->recordPackaging($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
