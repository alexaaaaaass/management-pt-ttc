<?php

namespace App\Filament\Admin\Resources\ImrFinishResource\Pages;

use App\Filament\Admin\Resources\ImrFinishResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditImrFinish extends EditRecord
{
    protected static string $resource = ImrFinishResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
