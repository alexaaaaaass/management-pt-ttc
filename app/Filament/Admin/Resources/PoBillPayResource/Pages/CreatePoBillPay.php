<?php

namespace App\Filament\Admin\Resources\PoBillPayResource\Pages;

use App\Filament\Admin\Resources\PoBillPayResource;
use App\Models\PoBilling;
use App\Services\Finance\PoBillPayService;
use App\Support\MoneyHelper;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreatePoBillPay extends CreateRecord
{
    protected static string $resource = PoBillPayResource::class;

    public function mount(): void
    {
        parent::mount();

        $billingId = request()->query('po_billing_id');
        if (! $billingId) {
            return;
        }

        $bill = PoBilling::find($billingId);
        if (! $bill || $bill->sisa_tagihan <= 0) {
            return;
        }

        $sisaFormatted = MoneyHelper::format($bill->sisa_tagihan, 2) ?? '0';
        $data = $this->form->getState();
        $data['po_billing_id'] = $bill->id;
        $data['gudang'] = $data['gudang'] ?: 'UGRMS';
        $data['periode'] = $bill->periode;
        $data['total_tagihan'] = $sisaFormatted;
        $data['total_pembayaran'] = $sisaFormatted;
        $data['items'] = [
            (string) Str::uuid() => [
                'metode_bayar_id'    => null,
                'nominal_pembayaran' => $sisaFormatted,
                'coa_debit_id'       => null,
                'coa_kredit_id'      => null,
                'mata_uang'          => 'IDR',
                'nama_bank'          => null,
                'no_rekening'        => null,
                'atas_nama'          => null,
                'memo'               => 'Pelunasan sisa tagihan ' . $bill->kode_tagihan,
            ],
        ];

        $this->form->fill($data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(PoBillPayService::class)->recordPayment($data);
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->implode(' | ');
            Notification::make()
                ->title('Gagal Menyimpan Pembayaran')
                ->body($msg)
                ->danger()
                ->send();

            $this->halt();
            throw $e;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Terjadi Kesalahan')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
