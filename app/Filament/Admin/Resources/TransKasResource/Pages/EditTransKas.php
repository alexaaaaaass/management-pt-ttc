<?php

namespace App\Filament\Admin\Resources\TransKasResource\Pages;

use App\Filament\Admin\Resources\TransKasResource;
use App\Services\Finance\TreasuryService;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTransKas extends EditRecord
{
    protected static string $resource = TransKasResource::class;

    public function getHeading(): string
    {
        return $this->record?->tipe_transaksi === 'KAS_KELUAR'
            ? 'Transaksi Kas Keluar'
            : 'Transaksi Kas Masuk';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->using(fn ($record) => app(TreasuryService::class)->deleteKas($record)),
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
        return app(TreasuryService::class)->updateKas($record, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index', [
            'activeTab' => $this->record?->tipe_transaksi ?? 'KAS_MASUK',
        ]);
    }
}
