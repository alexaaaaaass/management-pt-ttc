<?php

namespace App\Filament\Admin\Resources\TransKasBankResource\Pages;

use App\Filament\Admin\Resources\TransKasBankResource;
use App\Services\Finance\TreasuryService;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTransKasBank extends EditRecord
{
    protected static string $resource = TransKasBankResource::class;

    public function getHeading(): string
    {
        return $this->record?->tipe_transaksi === 'BANK_KELUAR'
            ? 'Transaksi Bank Keluar'
            : 'Transaksi Bank Masuk';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->using(fn ($record) => app(TreasuryService::class)->deleteBank($record)),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['tanggal_transaksi'])) {
            $data['periode'] = Carbon::parse($data['tanggal_transaksi'])->format('F Y');
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(TreasuryService::class)->updateBank($record, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index', [
            'activeTab' => $this->record?->tipe_transaksi ?? 'BANK_MASUK',
        ]);
    }
}
