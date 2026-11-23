<?php

namespace App\Filament\Admin\Resources\PracetakProcessResource\Pages;

use App\Filament\Admin\Resources\IMRPrintingResource;
use App\Filament\Admin\Resources\PracetakProcessResource;
use App\Services\Pracetak\PracetakCostingService;
use App\Services\Pracetak\PracetakStockService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPracetakProcess extends EditRecord
{
    protected static string $resource = PracetakProcessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('postUsages')
                ->label('Posting Potong Stok Plate')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Posting Pemakaian Material Plate (OUT)')
                ->modalDescription('Simpan perubahan terlebih dahulu sebelum posting. Semua baris pemakaian plate berstatus Draft akan langsung memotong Stok Material Plate & tercatat di Histori Ledger (OUT).')
                ->visible(fn () => $this->record->has_unposted_usages)
                ->action(function () {
                    try {
                        $count = app(PracetakStockService::class)->postAllDraftUsages($this->record, auth()->id());
                        $this->refreshFormData([
                            'actual_material_cost',
                            'actual_total_cost',
                            'cost_variance',
                            'materialUsages',
                        ]);

                        Notification::make()
                            ->title("Berhasil Posting {$count} Pemakaian Material Plate")
                            ->body('Stok Prepress telah dipotong otomatis dan tercatat di Ledger OUT.')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Posting Pemakaian Material')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('syncArchive')
                ->label('Simpan ke Arsip Rak')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, ['plat_making', 'selesai'], true))
                ->action(function () {
                    if ($this->record->status === 'plat_making') {
                        $this->record->update(['status' => 'selesai']);
                    }
                    $costing = app(PracetakCostingService::class);
                    $costing->recalculateProcessCost($this->record);
                    $archive = $costing->syncPlateArchive($this->record->fresh());

                    Notification::make()
                        ->title('Arsip Plate Jadi Diperbarui')
                        ->body("Kode Arsip: {$archive->kode_arsip} (Lokasi: " . ($archive->lokasi_rak ?: 'Belum diisi') . ')')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('createImrForHandover')
                ->label('Ajukan IMR Printing (Serah Terima)')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->visible(fn () => $this->record->status === 'selesai' && filled($this->record->spk_id))
                ->url(fn () => IMRPrintingResource::getUrl('create', ['spk_id' => $this->record->spk_id])),

            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $costing = app(PracetakCostingService::class);
        $costing->recalculateProcessCost($this->record);

        if (in_array($this->record->status, ['selesai', 'handed_over'], true)) {
            $costing->syncPlateArchive($this->record->fresh());
        }
    }
}
