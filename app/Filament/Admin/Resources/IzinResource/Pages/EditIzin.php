<?php

namespace App\Filament\Admin\Resources\IzinResource\Pages;

use App\Filament\Admin\Resources\IzinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIzin extends EditRecord
{
    protected static string $resource = IzinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
