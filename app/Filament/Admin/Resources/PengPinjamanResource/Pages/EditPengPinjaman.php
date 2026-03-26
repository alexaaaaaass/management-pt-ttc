<?php

namespace App\Filament\Admin\Resources\PengPinjamanResource\Pages;

use App\Filament\Admin\Resources\PengPinjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengPinjaman extends EditRecord
{
    protected static string $resource = PengPinjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
