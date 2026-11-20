<?php

namespace App\Filament\Admin\Resources\PracetakPlateArchiveResource\Pages;

use App\Filament\Admin\Resources\PracetakPlateArchiveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPracetakPlateArchive extends EditRecord
{
    protected static string $resource = PracetakPlateArchiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
