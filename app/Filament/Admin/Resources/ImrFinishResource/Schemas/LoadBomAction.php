<?php

namespace App\Filament\Admin\Resources\ImrFinishResource\Schemas;

use App\Models\MasterSpk;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Set;
use Filament\Notifications\Notification;

class LoadBomAction
{
    public static function make(): Action
    {
        return Action::make('loadBom')
            ->label('Load Material IMR')
            ->icon('heroicon-o-arrow-down-tray')
            ->requiresConfirmation()
            ->modalHeading('Load Material dari SPK')
            ->action(function (Set $set, $get): void {
                $spkId = $get('../../spk_id') ?? $get('spk_id');

                if (! $spkId) {
                    Notification::make()
                        ->title('SPK belum dipilih')
                        ->body('Pilih Surat Perintah Kerja terlebih dahulu.')
                        ->warning()
                        ->send();

                    return;
                }

                $spk = MasterSpk::with([
                    'salesOrder.itemable.materials.material.satuan',
                    'salesOrder.itemable.materials.departemen',
                ])->find($spkId);

                if (! $spk || ! $spk->salesOrder || ! $spk->salesOrder->itemable) {
                    Notification::make()
                        ->title('Data SPK tidak lengkap')
                        ->body('SPK atau produk terkait tidak ditemukan.')
                        ->danger()
                        ->send();

                    return;
                }

                $materials = $spk->salesOrder->itemable->materials;

                if ($materials->isEmpty()) {
                    Notification::make()
                        ->title('BOM kosong')
                        ->body('Tidak ditemukan material pada BOM produk di SPK tersebut.')
                        ->warning()
                        ->send();

                    return;
                }

                $items = [];

                foreach ($materials as $bom) {
                    if (! $bom->material) {
                        continue;
                    }

                    $items[] = [
                        'item_id' => $bom->master_item_id,
                        'nama_item' => $bom->material->nama_master_item ?? '-',
                        'department' => $bom->departemen->nama_departemen ?? '-',
                        'satuan' => $bom->material->satuan->nama_satuan ?? '-',
                        'total_pesanan' => (float) $bom->qty
                            * (float) $spk->salesOrder->qty,
                        'qty_approved' => 0,
                        'qty_request' => 0,
                        'qty_input' => 0,
                        'catatan' => null,
                    ];
                }

                if (empty($items)) {
                    Notification::make()
                        ->title('Material tidak valid')
                        ->body('Tidak ada material BOM yang dapat digunakan.')
                        ->warning()
                        ->send();

                    return;
                }

                $set('../../imr_items', $items);

                Notification::make()
                    ->title('Material berhasil dimuat')
                    ->body(count($items) . ' material dimuat dari BOM SPK.')
                    ->success()
                    ->send();
            });
    }
}