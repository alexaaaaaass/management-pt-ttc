<?php

namespace App\Filament\Admin\Resources\GajiResource\Pages;

use App\Filament\Admin\Resources\GajiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGaji extends EditRecord
{
    protected static string $resource = GajiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
