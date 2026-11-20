<?php

namespace App\Filament\Admin\Resources\PracetakPenerimaanResource\Pages;

use App\Filament\Admin\Resources\PracetakPenerimaanResource;
use App\Services\Pracetak\PracetakStockService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPracetakPenerimaan extends ViewRecord
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

                        $this->fillForm();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Posting Penerimaan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\EditAction::make()->visible(fn () => $this->getRecord()->isDraft()),
        ];
    }
}
