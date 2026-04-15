<?php

namespace App\Filament\Admin\Resources\DieMakingResource\Pages;

use App\Filament\Admin\Resources\DieMakingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDieMaking extends EditRecord
{
    protected static string $resource = DieMakingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
