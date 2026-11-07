<?php

namespace App\Filament\Admin\Resources\PoBillingResource\Pages;

use App\Filament\Admin\Resources\PoBillingResource;
use App\Services\Finance\PoBillingService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditPoBilling extends EditRecord
{
    protected static string $resource = PoBillingResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['items'] = $this->record->items->toArray();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return app(PoBillingService::class)->updateBilling($record, $data);
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->implode(' | ');
            Notification::make()
                ->title('Gagal Memperbarui Tagihan')
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
                ->hidden(fn ($record) => !app(PoBillingService::class)->canDelete($record))
                ->using(fn ($record) => app(PoBillingService::class)->deleteBilling($record)),
        ];
    }
}
