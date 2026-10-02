<?php

namespace App\Filament\Admin\Resources\IssueMaterialResource\Pages;

use App\Filament\Admin\Resources\IssueMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIssueMaterial extends EditRecord
{
    protected static string $resource = IssueMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
