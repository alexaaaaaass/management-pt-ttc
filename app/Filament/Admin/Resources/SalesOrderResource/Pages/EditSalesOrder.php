<?php

namespace App\Filament\Admin\Resources\SalesOrderResource\Pages;

use App\Filament\Admin\Resources\SalesOrderResource;
use App\Services\Sales\SalesOrderService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditSalesOrder extends EditRecord
{
    protected static string $resource = SalesOrderResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->order_type === 'SPK') {
            $data['item_source'] = $this->record->itemable_id;

            $qty = (float) ($this->record->qty ?? 0);
            if ($this->record->itemable_id) {
                $data['bom_items'] = app(SalesOrderService::class)->getBomPreview((int) $this->record->itemable_id, $qty);
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        $canModify = app(SalesOrderService::class)->canModify($this->record);

        return [
            Actions\DeleteAction::make()
                ->disabled(! $canModify)
                ->tooltip(! $canModify ? 'Sales Order tidak dapat dihapus karena sudah memiliki Surat Jalan atau SPK terkait.' : null),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $materialItems = $data['items'] ?? [];
        unset($data['items']);

        try {
            return app(SalesOrderService::class)->updateSalesOrder($record, $data, $materialItems);
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->implode(' | ');
            Notification::make()
                ->title('Gagal Memperbarui Sales Order')
                ->body($msg)
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
