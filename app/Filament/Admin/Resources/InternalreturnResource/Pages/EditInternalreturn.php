<?php

namespace App\Filament\Admin\Resources\InternalreturnResource\Pages;

use App\Filament\Admin\Resources\InternalreturnResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInternalreturn extends EditRecord
{
    protected static string $resource = InternalreturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
