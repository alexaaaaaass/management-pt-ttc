<?php

namespace App\Filament\Admin\Resources\ErrorProductionResource\Pages;

use App\Filament\Admin\Resources\ErrorProductionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditErrorProduction extends EditRecord
{
    protected static string $resource = ErrorProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
