<?php

namespace App\Filament\Admin\Resources\PoBillPayResource\Pages;

use App\Filament\Admin\Resources\PoBillPayResource;
use App\Services\Finance\PoBillPayService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreatePoBillPay extends CreateRecord
{
    protected static string $resource = PoBillPayResource::class;

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
