<?php

namespace App\Filament\Admin\Resources\CutiResource\Pages;

use App\Filament\Admin\Resources\CutiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCuti extends EditRecord
{
    protected static string $resource = CutiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
