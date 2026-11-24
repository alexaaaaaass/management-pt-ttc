<?php

namespace App\Filament\Admin\Resources\TransKasResource\Pages;

use App\Filament\Admin\Resources\TransKasResource;
use App\Services\Finance\TreasuryService;
use Carbon\Carbon;
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
            'tipe_transaksi'    => $this->jenisTransaksi,
            'kode_transaksi'    => 'AUTO',
            'tanggal_transaksi' => now()->toDateString(),
            'periode'           => now()->format('F Y'),
            'gudang'            => 'UGRMS',
            'account_kas_id'    => TransKasResource::getDefaultKasCoaId(),
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
        return app(TreasuryService::class)->recordKas($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index', [
            'activeTab' => $this->record?->tipe_transaksi ?? $this->jenisTransaksi,
        ]);
    }
}
