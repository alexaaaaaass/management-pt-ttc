<?php

namespace App\Filament\Admin\Resources\OperatorDiemakingResource\Pages;

use App\Filament\Admin\Resources\OperatorDiemakingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOperatorDiemaking extends EditRecord
{
    protected static string $resource = OperatorDiemakingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
