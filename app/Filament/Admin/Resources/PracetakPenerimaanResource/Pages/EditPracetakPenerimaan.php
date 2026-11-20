<?php

namespace App\Filament\Admin\Resources\PracetakPenerimaanResource\Pages;

use App\Filament\Admin\Resources\PracetakPenerimaanResource;
use App\Models\PracetakPenerimaan;
use App\Services\Pracetak\PracetakStockService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPracetakPenerimaan extends EditRecord
{
    protected static string $resource = PracetakPenerimaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('post')
                ->label('Posting')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->getRecord()->isDraft())
                ->requiresConfirmation()
                ->modalHeading('Posting Penerimaan Material Plate')
                ->modalDescription('Apakah Anda yakin ingin memposting penerimaan ini? Stok sheet utuh akan bertambah dan mutasi akan dicatat ke Ledger Histori Pra Cetak.')
                ->action(function () {
                    try {
                        app(PracetakStockService::class)->postPenerimaan($this->getRecord(), auth()->id());

                        Notification::make()
                            ->title('Penerimaan Berhasil Diposting')
                            ->body("Transaksi {$this->getRecord()->nomor_penerimaan} berhasil diposting. Stok plate telah diperbarui.")
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Posting Penerimaan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\ViewAction::make(),
            Actions\DeleteAction::make()->visible(fn () => $this->getRecord()->isDraft()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $totalQty = 0;
        $totalNominal = 0;
        if (!empty($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $qty = (float) ($item['qty_sheet'] ?? 0);
                $sub = (float) ($item['subtotal'] ?? 0);
                $totalQty += $qty;
                $totalNominal += $sub;
            }
        }
        $data['total_qty'] = $totalQty;
        $data['total_nominal'] = $totalNominal;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
