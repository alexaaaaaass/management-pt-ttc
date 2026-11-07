<?php

namespace App\Filament\Admin\Resources\PoBillPayResource\Pages;

use App\Filament\Admin\Resources\PoBillPayResource;
use App\Services\Finance\PoBillPayService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditPoBillPay extends EditRecord
{
    protected static string $resource = PoBillPayResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['items'] = $this->record->items->toArray();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return app(PoBillPayService::class)->updatePayment($record, $data);
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->implode(' | ');
            Notification::make()
                ->title('Gagal Memperbarui Pembayaran')
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

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->using(fn ($record) => app(PoBillPayService::class)->deletePayment($record)),
        ];
    }
}
