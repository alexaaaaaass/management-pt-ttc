<?php

namespace App\Filament\Admin\Resources\SalesOrderResource\Pages;

use App\Filament\Admin\Resources\SalesOrderResource;
use App\Services\Sales\SalesOrderService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateSalesOrder extends CreateRecord
{
    protected static string $resource = SalesOrderResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $materialItems = $data['items'] ?? [];
        unset($data['items']);

        try {
            return app(SalesOrderService::class)->createSalesOrder($data, $materialItems);
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->implode(' | ');
            Notification::make()
                ->title('Gagal Menyimpan Sales Order')
                ->body($msg)
                ->danger()
                ->send();

            $this->halt();
            throw $e;
        }
    }
}