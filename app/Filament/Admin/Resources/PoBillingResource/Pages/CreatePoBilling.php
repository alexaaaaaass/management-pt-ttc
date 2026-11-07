<?php

namespace App\Filament\Admin\Resources\PoBillingResource\Pages;

use App\Filament\Admin\Resources\PoBillingResource;
use App\Services\Finance\PoBillingService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreatePoBilling extends CreateRecord
{
    protected static string $resource = PoBillingResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(PoBillingService::class)->createBilling($data);
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->implode(' | ');
            Notification::make()
                ->title('Gagal Menyimpan Tagihan')
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
