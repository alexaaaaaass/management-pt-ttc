<?php

namespace App\Filament\Admin\Resources\ExternalReturnResource\Pages;

use App\Filament\Admin\Resources\ExternalReturnResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExternalReturn extends EditRecord
{
    protected static string $resource = ExternalReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
