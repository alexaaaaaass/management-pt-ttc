<?php

namespace App\Filament\Admin\Resources\TransKasResource\Pages;

use App\Filament\Admin\Resources\TransKasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTransKas extends CreateRecord
{
    protected static string $resource = TransKasResource::class;

    // Mengisi form secara otomatis dari URL (?type=) sebelum disimpan ke database
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tipe_transaksi'] = request()->query('type', 'KAS_MASUK');
        return $data;
    }

    protected function getFormActions(): array
    {
        return parent::getFormActions();
    }

    // Mengisi data awal tipe_transaksi dari URL saat halaman Create pertama kali dibuka
    public function mount(): void
    {
        parent::mount();
        
        $type = request()->query('type', 'KAS_MASUK');
        $this->form->fill([
            'tipe_transaksi' => $type,
        ]);
    }
}