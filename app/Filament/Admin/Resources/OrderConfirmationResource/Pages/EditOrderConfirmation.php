<?php

namespace App\Filament\Admin\Resources\OrderConfirmationResource\Pages;

use App\Filament\Admin\Resources\OrderConfirmationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderConfirmation extends EditRecord
{
    protected static string $resource = OrderConfirmationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview_pdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn () => route('order-confirmation.preview', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('download_pdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->url(fn () => route('order-confirmation.download', $this->record)),

            Actions\Action::make('deal_convert')
                ->label('Deal -> Create SO')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Customer Deal & Lanjut Buat Sales Order')
                ->modalDescription('Apakah Customer telah menyetujui Order Confirmation ini? Status Order Confirmation akan ditandai DEAL dan Anda akan langsung diarahkan ke form pembuatan Sales Order untuk melengkapi data komersial dan BOM.')
                ->modalSubmitActionLabel('Ya, Lanjut ke Form Sales Order')
                ->visible(fn () => !in_array($this->record->status, ['converted_to_so', 'rejected']))
                ->action(function () {
                    if ($this->record->sales_order_id && $this->record->salesOrder) {
                        \Filament\Notifications\Notification::make()
                            ->title('Sales Order Sudah Ada')
                            ->body("Order Confirmation ini sudah terhubung ke Sales Order No: {$this->record->salesOrder->no_sales_order}. Mengarahkan ke halaman edit...")
                            ->warning()
                            ->send();

                        return redirect(\App\Filament\Admin\Resources\SalesOrderResource::getUrl('edit', ['record' => $this->record->sales_order_id]));
                    }

                    $this->record->update(['status' => 'deal']);

                    \Filament\Notifications\Notification::make()
                        ->title('Status Order Confirmation: Customer Deal')
                        ->body('Silakan periksa dan lengkapi data Sales Order (mata uang, syarat pembayaran, toleransi, dll) sebelum disimpan.')
                        ->success()
                        ->send();

                    return redirect(\App\Filament\Admin\Resources\SalesOrderResource::getUrl('create', ['oc_id' => $this->record->id]));
                }),

            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (empty($data['disetujui_customer'])) {
            $data['disetujui_customer'] = $this->record->customer?->nama_customer ?: $this->record->customer?->nama_pic;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->recalculateTotals();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
