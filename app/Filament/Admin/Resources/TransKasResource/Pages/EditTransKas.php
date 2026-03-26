<?php

namespace App\Filament\Admin\Resources\TransKasResource\Pages;

use App\Filament\Admin\Resources\TransKasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransKas extends EditRecord
{
    protected static string $resource = TransKasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
