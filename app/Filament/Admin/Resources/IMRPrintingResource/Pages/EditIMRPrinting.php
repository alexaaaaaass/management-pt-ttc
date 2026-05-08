<?php

namespace App\Filament\Admin\Resources\IMRPrintingResource\Pages;

use App\Filament\Admin\Resources\IMRPrintingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIMRPrinting extends EditRecord
{
    protected static string $resource = IMRPrintingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
