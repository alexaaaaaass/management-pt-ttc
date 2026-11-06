<?php

namespace App\Filament\Admin\Resources\SalesOrderResource\Pages;

use App\Filament\Admin\Resources\SalesOrderResource;
use App\Models\FinishGoodItem;
use App\Models\OrderConfirmation;
use App\Services\Sales\SalesOrderService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateSalesOrder extends CreateRecord
{
    protected static string $resource = SalesOrderResource::class;

    public ?int $orderConfirmationId = null;

    public function mount(): void
    {
        parent::mount();

        $ocId = request()->query('oc_id');
        if ($ocId) {
            $this->orderConfirmationId = (int) $ocId;
            $oc = OrderConfirmation::with(['customer', 'items.finishGoodItem.typeItem'])->find($ocId);
            if ($oc) {
                $salesOrderService = app(SalesOrderService::class);
                $marketingService = app(\App\Services\Marketing\MarketingWorkflowService::class);

                $formData = $salesOrderService->prepareFormDataFromOrderConfirmation($oc);
                $this->form->fill($formData);

                $progress = $marketingService->getOrderConfirmationProgress($oc);
                $infoMsg = "Form terisi otomatis untuk item '{$formData['product_name']}' dari Order Confirmation {$oc->doc_no}.";
                if ($progress['total_items'] > 1) {
                    $infoMsg .= " (Memproses item ke-" . ($progress['converted_count'] + 1) . " dari {$progress['total_items']} item penawaran).";
                }

                Notification::make()
                    ->title('Data Order Confirmation Dimuat')
                    ->body($infoMsg)
                    ->info()
                    ->send();
            }
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        $materialItems = $data['items'] ?? [];
        unset($data['items']);

        if (! empty($data['order_confirmation_id'])) {
            $this->orderConfirmationId = (int) $data['order_confirmation_id'];
        }
        unset($data['order_confirmation_item_id']);
        unset($data['bom_items']);

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

    protected function afterCreate(): void
    {
        $ocId = $this->orderConfirmationId ?: ($this->record->order_confirmation_id ?? null);
        if ($ocId) {
            $oc = OrderConfirmation::find($ocId);
            if ($oc) {
                $marketingService = app(\App\Services\Marketing\MarketingWorkflowService::class);
                $oc = $marketingService->syncOrderConfirmationStatus($oc, $this->record->id);
                $progress = $marketingService->getOrderConfirmationProgress($oc);

                if ($progress['total_items'] > 1 && $progress['unconverted_count'] > 0) {
                    Notification::make()
                        ->title("Sales Order Berhasil Disimpan ({$progress['converted_count']}/{$progress['total_items']} Item)")
                        ->body("Order Confirmation {$oc->doc_no} masih memiliki {$progress['unconverted_count']} item penawaran yang belum diterbitkan Sales Order-nya.")
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('create_next_so')
                                ->label('Lanjut Buat SO untuk Item Selanjutnya')
                                ->button()
                                ->color('primary')
                                ->url(SalesOrderResource::getUrl('create', ['oc_id' => $oc->id])),
                        ])
                        ->persistent()
                        ->success()
                        ->send();
                }
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}