<?php

namespace App\Filament\Admin\Resources\FinishGoodItemResource\Pages;

use App\Filament\Admin\Resources\FinishGoodItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFinishGoodItem extends EditRecord
{
    protected static string $resource = FinishGoodItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (empty($data['kertas_items'])) {
            if (!empty($this->record->spesifikasi_kertas) || !empty($this->record->gramasi) || !empty($this->record->up_satu)) {
                $data['kertas_items'] = [
                    [
                        'nama_kertas'    => $this->record->spesifikasi_kertas,
                        'bagian_kemasan' => $this->record->komponen ?: 'Utama',
                        'gramasi'        => $this->record->gramasi,
                        'tebal_kertas'   => $this->record->tebal_kertas,
                        'up'             => $this->record->up_satu,
                        'ukuran_cetak'   => $this->record->ukuran_cetak,
                    ]
                ];
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
