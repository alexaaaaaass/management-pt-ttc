<?php

namespace App\Filament\Admin\Resources\SuratPerintahKerjaResource\Pages;

use App\Filament\Admin\Resources\SuratPerintahKerjaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSuratPerintahKerja extends EditRecord
{
    protected static string $resource = SuratPerintahKerjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
