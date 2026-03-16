<?php

namespace App\Filament\Admin\Resources\MesinFinisResource\Pages;

use App\Filament\Admin\Resources\MesinFinisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMesinFinis extends EditRecord
{
    protected static string $resource = MesinFinisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
