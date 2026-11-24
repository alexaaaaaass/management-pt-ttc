<?php

namespace App\Filament\Admin\Resources\TransKasBankResource\Pages;

use App\Filament\Admin\Resources\TransKasBankResource;
use App\Services\Finance\TreasuryService;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTransKasBank extends CreateRecord
{
    protected static string $resource = TransKasBankResource::class;

    public string $jenisTransaksi = 'BANK_MASUK';

    public function getHeading(): string
    {
        return ($this->jenisTransaksi ?? request()->query('type', 'BANK_MASUK')) === 'BANK_KELUAR'
            ? 'Transaksi Bank Keluar'
            : 'Transaksi Bank Masuk';
    }

    public function mount(): void
    {
        parent::mount();

        $this->jenisTransaksi = request()->query('type', 'BANK_MASUK');

        $this->form->fill([
            'tipe_transaksi'    => $this->jenisTransaksi,
            'kode_transaksi'    => 'AUTO',
            'tanggal_transaksi' => now()->toDateString(),
            'periode'           => now()->format('F Y'),
            'gudang'            => 'UGRMS',
            'status'            => 'ACTIVE',
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tipe_transaksi'] = $this->jenisTransaksi;
        $data['status'] = $data['status'] ?? 'ACTIVE';
        $data['periode'] = ! empty($data['tanggal_transaksi'])
            ? Carbon::parse($data['tanggal_transaksi'])->format('F Y')
            : ($data['periode'] ?? now()->format('F Y'));

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(TreasuryService::class)->recordBank($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index', [
            'activeTab' => $this->record?->tipe_transaksi ?? $this->jenisTransaksi,
        ]);
    }
}
