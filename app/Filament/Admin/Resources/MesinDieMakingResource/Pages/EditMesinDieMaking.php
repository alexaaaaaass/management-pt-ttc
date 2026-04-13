<?php

namespace App\Filament\Admin\Resources\MesinDieMakingResource\Pages;

use App\Filament\Admin\Resources\MesinDieMakingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMesinDieMaking extends EditRecord
{
    protected static string $resource = MesinDieMakingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
