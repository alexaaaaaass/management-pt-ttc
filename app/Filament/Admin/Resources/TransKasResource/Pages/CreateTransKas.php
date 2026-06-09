<?php

namespace App\Filament\Admin\Resources\TransKasResource\Pages;

use App\Filament\Admin\Resources\TransKasResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransKas extends CreateRecord
{
    protected static string $resource =
        TransKasResource::class;

    public string $jenisTransaksi;

    public function mount(): void
    {
        parent::mount();

        $this->jenisTransaksi =
            request()->query(
                'type',
                'KAS_MASUK'
            );

        $this->form->fill([
            'tipe_transaksi' =>
                $this->jenisTransaksi,
        ]);
    }

    protected function mutateFormDataBeforeCreate(
        array $data
    ): array {

        $data['tipe_transaksi'] =
            $this->jenisTransaksi;

        return $data;
    }
}