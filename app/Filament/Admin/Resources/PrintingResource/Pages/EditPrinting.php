<?php

namespace App\Filament\Admin\Resources\PrintingResource\Pages;

use App\Filament\Admin\Resources\PrintingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrinting extends EditRecord
{
    protected static string $resource = PrintingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
