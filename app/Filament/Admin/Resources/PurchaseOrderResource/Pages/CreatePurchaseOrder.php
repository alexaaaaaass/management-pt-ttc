<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\Pages;

use App\Filament\Admin\Resources\PurchaseOrderResource;
use App\Services\PurchaseOrder\PurchaseOrderService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    /** @var Collection<int, \App\Models\PurchaseOrder>|null */
    protected ?Collection $createdPurchaseOrders = null;

    protected function handleRecordCreation(array $data): Model
    {
        $items = $data['items'] ?? [];
        unset($data['items'], $data['pr_preview']);

        try {
            $this->createdPurchaseOrders = app(PurchaseOrderService::class)
                ->createMultiSupplierPurchaseOrders($data, $items);

            return $this->createdPurchaseOrders->first();
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->implode(' | ');

            Notification::make()
                ->title('Gagal Membuat Purchase Order')
                ->body($msg)
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
            throw $e;
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        $pos = $this->createdPurchaseOrders;
        if (! $pos || $pos->isEmpty()) {
            return parent::getCreatedNotification();
        }

        if ($pos->count() === 1) {
            $po = $pos->first();
            $supplierName = $po->supplier?->nama_supplier ?? '-';

            return Notification::make()
                ->success()
                ->title('Purchase Order Berhasil Dibuat')
                ->body("{$po->no_po} — {$supplierName}");
        }

        $summary = $pos
            ->map(fn ($po) => "{$po->no_po} ({$po->supplier?->nama_supplier})")
            ->implode(' | ');

        return Notification::make()
            ->success()
            ->title("{$pos->count()} Purchase Order Resmi Berhasil Diterbitkan")
            ->body($summary)
            ->duration(10000);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
