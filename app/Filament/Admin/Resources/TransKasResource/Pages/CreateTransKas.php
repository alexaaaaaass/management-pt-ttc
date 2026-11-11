<?php

namespace App\Filament\Admin\Resources\TransKasResource\Pages;

use App\Filament\Admin\Resources\TransKasResource;
use App\Services\Finance\TreasuryService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTransKas extends CreateRecord
{
    protected static string $resource = TransKasResource::class;

    public string $jenisTransaksi = 'KAS_MASUK';

    public function getHeading(): string
    {
        return ($this->jenisTransaksi ?? request()->query('type', 'KAS_MASUK')) === 'KAS_KELUAR'
            ? 'Transaksi Kas Keluar'
            : 'Transaksi Kas Masuk';
    }

    public function mount(): void
    {
        parent::mount();

        $this->jenisTransaksi = request()->query('type', 'KAS_MASUK');

        $this->form->fill([
            'tipe_transaksi' => $this->jenisTransaksi,
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tipe_transaksi'] = $this->jenisTransaksi;
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(TreasuryService::class)->recordKas($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
