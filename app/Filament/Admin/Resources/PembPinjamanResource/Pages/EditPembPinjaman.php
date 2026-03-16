<?php

namespace App\Filament\Admin\Resources\PembPinjamanResource\Pages;

use App\Filament\Admin\Resources\PembPinjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPembPinjaman extends EditRecord
{
    protected static string $resource = PembPinjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
