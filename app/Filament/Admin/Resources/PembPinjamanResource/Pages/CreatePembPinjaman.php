<?php

namespace App\Filament\Admin\Resources\PembPinjamanResource\Pages;

use App\Filament\Admin\Resources\PembPinjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePembPinjaman extends CreateRecord
{
    protected static string $resource = PembPinjamanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
